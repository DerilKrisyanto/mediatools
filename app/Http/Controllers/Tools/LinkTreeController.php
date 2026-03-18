<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Linktree;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\LinktreeOrder;
use Illuminate\Support\Facades\Log;

class LinkTreeController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /* ── INDEX ─────────────────────────────────────────── */
    public function index()
    {
        $items = Linktree::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>', now());
            })
            ->orderBy('visitors', 'desc')
            ->limit(50)
            ->get();

        $userLinktree = null;

        if (Auth::check()) {
            $userLinktree = Linktree::where('user_id', Auth::id())
                ->latest()
                ->first();
        }

        return view('tools.linktree.index', compact('items', 'userLinktree'));
    }

    /* ── SHOW (Public Page) ─────────────────────────────── */
    public function show(string $unique_id)
    {
        $data = Linktree::where('unique_id', $unique_id)->first();

        if (!$data || !$data->is_active) abort(404);

        if ($data->expired_at && Carbon::now()->gt($data->expired_at)) abort(404);

        $data->increment('visitors');

        $profile = [
            'username' => $data->username,
            'name'     => $data->name,
            'bio'      => $data->bio,
            'avatar'   => $data->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($data->name) . '&background=a3e635&color=0f172a&bold=true&size=200',
            'verified' => $data->verified,
            'visitors' => number_format($data->visitors),
        ];

        $links   = $data->links_data   ?: [];
        $socials = $data->socials_data ?: [];

        // Pass selected template to the view
        $pageTemplate = $data->page_template; // 'dark' | 'light' | 'neon'

        return view('tools.linktree.view_page', compact(
            'profile', 'links', 'socials', 'unique_id', 'pageTemplate'
        ));
    }

    /* ── CHECK PLAN ─────────────────────────────────────── */
    public function checkPlan()
    {
        $userId = Auth::id();

        $activeLinktree = Linktree::where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>', now());
            })
            ->latest()
            ->first();

        return response()->json([
            'has_plan' => (bool) $activeLinktree,
            'linktree' => $activeLinktree ? $activeLinktree->toArray() : null,
        ]);
    }

    /* ── STORE (Create / Update) ─────────────────────────── */
    public function store(Request $request)
    {
        $request->validate([
            'username'  => 'required|string|max:25',
            'name'      => 'required|string|max:50',
            'plan_type' => 'required',
        ]);

        $userId = Auth::id();

        $planData = [
            'starter'    => ['price' => 19900,  'duration' => 1],
            'best_value' => ['price' => 89000,  'duration' => 6],
            'business'   => ['price' => 149000, 'duration' => 12],
        ];

        // Resolve and validate template
        $template = in_array($request->page_template, Linktree::TEMPLATES)
            ? $request->page_template
            : 'dark';

        // Build links JSON
        $links = [];
        if ($request->web_url) {
            $links[] = [
                'title' => 'Website',
                'url'   => $request->web_url,
                'icon'  => 'fa-globe',
            ];
        }

        // Build socials JSON
        $socials = [];
        if ($request->ig_user)   $socials[] = ['icon' => 'fa-instagram', 'url' => 'https://instagram.com/'  . ltrim($request->ig_user, '@')];
        if ($request->tt_user)   $socials[] = ['icon' => 'fa-tiktok',    'url' => 'https://tiktok.com/@'   . ltrim($request->tt_user, '@')];
        if ($request->wa_number) $socials[] = ['icon' => 'fa-whatsapp',  'url' => 'https://wa.me/'         . preg_replace('/\D/', '', $request->wa_number)];

        // ── EDIT MODE (user has active plan) ─────────────
        $existingActive = Linktree::where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>', now());
            })
            ->first();

        if ($existingActive && $request->plan_type === 'existing') {
            $existingActive->update([
                'name'          => $request->name,
                'username'      => '@' . ltrim($request->username, '@'),
                'bio'           => $request->bio,
                'avatar'        => $request->avatar_base64 ?: $existingActive->avatar,
                'links_data'    => $links,
                'socials_data'  => $socials,
                'page_template' => $template,
            ]);

            return response()->json(['success' => true, 'payment_needed' => false]);
        }

        // ── CREATE MODE (new linktree, payment required) ──
        if (!isset($planData[$request->plan_type])) {
            return response()->json(['success' => false, 'message' => 'Paket tidak valid.'], 422);
        }

        $unique_id = Str::slug($request->username) . '-' . rand(100, 999);

        $linktree = Linktree::create([
            'user_id'       => $userId,
            'unique_id'     => $unique_id,
            'name'          => $request->name,
            'username'      => '@' . ltrim($request->username, '@'),
            'bio'           => $request->bio,
            'avatar'        => $request->avatar_base64,
            'links_data'    => $links,
            'socials_data'  => $socials,
            'page_template' => $template,
            'is_active'     => false, // Activated after payment
            'plan_type'     => $request->plan_type,
        ]);

        $plans = [
            'starter'    => 'Starter 1 Bulan — Rp19.900',
            'best_value' => 'Best Value 6 Bulan — Rp89.000',
            'business'   => 'Business 12 Bulan — Rp149.000',
        ];

        try {
            $orderId = 'LT-' . time() . '-' . $linktree->id;
            $amount  = $planData[$request->plan_type]['price'];

            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email'      => Auth::user()->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            LinktreeOrder::create([
                'user_id'     => $userId,
                'linktree_id' => $linktree->id,
                'order_id'    => $orderId,
                'plan_type'   => $request->plan_type,
                'amount'      => $amount,
                'snap_token'  => $snapToken,
            ]);

            return response()->json([
                'success'         => true,
                'payment_needed'  => true,
                'method'          => 'midtrans',
                'snap_token'      => $snapToken,
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans error: ' . $e->getMessage());

            $message = "Halo Admin MediaTools!\n\n"
                . "Saya ingin aktivasi Linktree secara manual.\n"
                . "User     : " . Auth::user()->name . "\n"
                . "Email    : " . Auth::user()->email . "\n"
                . "ID       : " . $unique_id . "\n"
                . "Paket    : " . ($plans[$request->plan_type] ?? $request->plan_type) . "\n"
                . "Template : " . $template;

            return response()->json([
                'success'        => true,
                'payment_needed' => true,
                'method'         => 'whatsapp',
                'payment_url'    => 'https://wa.me/6289610047788?text=' . urlencode($message),
            ]);
        }
    }

    /* ── MIDTRANS WEBHOOK ────────────────────────────────── */
    public function midtransNotification(Request $request)
    {
        $serverKey   = config('services.midtrans.server_key');
        $orderId     = $request->order_id;
        $statusCode  = $request->status_code;
        $grossAmount = $request->gross_amount;

        // Validate signature
        $localSig = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($request->signature_key !== $localSig) {
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        $order = LinktreeOrder::where('order_id', $orderId)->first();
        if (!$order) return response()->json(['message' => 'Order not found'], 404);

        $transaction = $request->transaction_status;

        if (in_array($transaction, ['settlement', 'capture'])) {

            if ($order->status !== 'success') {
                $order->update(['status' => 'success']);

                $monthsToAdd = match ($order->plan_type) {
                    'starter'    => 1,
                    'best_value' => 6,
                    'business'   => 12,
                    default      => 0,
                };

                $linktree = Linktree::find($order->linktree_id);

                if ($linktree) {
                    $baseTime = ($linktree->expired_at && $linktree->expired_at->isFuture())
                        ? $linktree->expired_at
                        : now();

                    $linktree->update([
                        'is_active'  => true,
                        'verified'   => true,
                        'expired_at' => $baseTime->addMonths($monthsToAdd),
                    ]);
                }
            }

        } elseif (in_array($transaction, ['expire', 'cancel', 'deny'])) {
            $order->update(['status' => 'failed']);
        }

        return response()->json(['status' => 'OK']);
    }
}