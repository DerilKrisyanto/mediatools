<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller
{
    public function index()
    {
        $signature = null;
        
        // Hanya ambil data dari database jika user sudah login
        if (Auth::check()) {
            $signature = Signature::where('user_id', Auth::id())->first();
        }
        
        return view('tools.signature.index', compact('signature'));
    }

    public function store(Request $request)
    {
        // Proteksi tambahan untuk request AJAX
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan login untuk menyimpan tanda tangan.'
            ], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'avatar_base64' => 'nullable|string'
        ]);

        $signature = Signature::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'name' => $request->name,
                'job_title' => $request->job_title,
                'company' => $request->company,
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'avatar' => $request->avatar_base64,
                'template_style' => $request->template_style ?? 'modern',
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Tanda tangan berhasil disimpan!'
        ]);
    }
}