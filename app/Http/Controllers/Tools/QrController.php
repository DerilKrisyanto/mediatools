<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Qr;
use Illuminate\Support\Facades\Auth;

class QrController extends Controller
{
    public function index()
    {
        // Mengambil data terakhir jika user login untuk kenyamanan
        $lastQr = Auth::check() ? Qr::where('user_id', Auth::id())->latest()->first() : null;
        return view('tools.qr.index', compact('lastQr'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:500',
            'settings' => 'required|json'
        ]);

        Qr::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'settings' => $request->settings,
        ]);

        return response()->json(['message' => 'QR Configuration synced!']);
    }
}