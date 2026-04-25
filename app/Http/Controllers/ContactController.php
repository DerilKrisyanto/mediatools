<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactMail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        if (! auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'redirect' => route('login'),
                    'message'  => 'Silakan login terlebih dahulu.',
                ], 401);
            }
            return redirect()->route('login')
                ->with('info', 'Silakan login untuk mengirim pesan ke tim kami.');
        }

        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|string|max:100',
            'message' => 'required|string|min:10|max:2000',
        ], [
            'name.required'    => 'Nama lengkap wajib diisi.',
            'email.required'   => 'Email wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
            'subject.required' => 'Topik wajib dipilih.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min'      => 'Pesan minimal 10 karakter.',
        ]);

        $validated['sender_name']  = auth()->user()->name;
        $validated['sender_email'] = auth()->user()->email;

        Log::info('[Contact] Mencoba kirim email', [
            'mailer'      => config('mail.default'),
            'from'        => config('mail.from.address'),
            'to'          => 'halo@mediatools.cloud',
            'subject'     => $validated['subject'],
            'sender_user' => $validated['sender_email'],
            'resend_key'  => config('resend.api_key')
                             ? 'ada (' . substr(config('resend.api_key'), 0, 8) . '...)'
                             : (env('RESEND_API_KEY') ? 'ada di env tapi belum di config' : 'TIDAK ADA'),
        ]);

        try {
            Mail::to('halo@mediatools.cloud')
                ->send(new ContactMail($validated));

            Log::info('[Contact] Email berhasil dikirim', [
                'sender' => $validated['sender_email'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim! Kami akan membalas dalam 1x24 jam.',
            ]);

        } catch (\Exception $e) {
            Log::error('[Contact] GAGAL kirim email', [
                'error'      => $e->getMessage(),
                'mailer'     => config('mail.default'),
                'resend_key' => env('RESEND_API_KEY') ? 'ada' : 'tidak ada',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan. Silakan hubungi kami langsung di halo@mediatools.cloud',
                'debug'   => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function newsletter(Request $request)
    {
        if (! auth()->check()) {
            return response()->json([
                'redirect' => route('register'),
                'message'  => 'Daftar akun gratis untuk subscribe newsletter kami.',
            ], 401);
        }

        $request->validate(['newsletter_email' => 'required|email|max:150']);

        Log::info('[Newsletter] Subscribe', ['email' => auth()->user()->email]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil! Cek inbox ' . auth()->user()->email . ' untuk konfirmasi.',
        ]);
    }
}