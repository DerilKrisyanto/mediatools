<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserLogoController extends Controller
{
    /**
     * Upload/ganti logo milik user yang sedang login.
     * Logo lama (jika ada) otomatis dihapus agar tidak menumpuk file yatim di storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'logo.required' => 'Silakan pilih file gambar logo terlebih dahulu.',
            'logo.image'    => 'File yang diunggah harus berupa gambar.',
            'logo.mimes'    => 'Format logo harus JPG, PNG, atau WEBP.',
            'logo.max'      => 'Ukuran logo maksimal 2MB.',
        ]);

        $user = Auth::user();

        // Hapus logo lama milik user ini (kalau ada) sebelum simpan yang baru
        if ($user->logo_path && Storage::disk('public')->exists($user->logo_path)) {
            Storage::disk('public')->delete($user->logo_path);
        }

        $ext  = $request->file('logo')->getClientOriginalExtension();
        $path = $request->file('logo')->storeAs(
            'logos',
            'user-' . $user->id . '-' . time() . '.' . $ext,
            'public'
        );

        $user->update(['logo_path' => $path]);

        return back()->with('success', 'Logo berhasil diunggah. Logo ini akan otomatis tampil di setiap cetakan memo Anda.');
    }

    /**
     * Hapus logo milik user yang sedang login.
     * Setelah dihapus, cetakan memo otomatis kembali memakai logo default sistem.
     */
    public function destroy(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->logo_path && Storage::disk('public')->exists($user->logo_path)) {
            Storage::disk('public')->delete($user->logo_path);
        }

        $user->update(['logo_path' => null]);

        return back()->with('success', 'Logo berhasil dihapus. Cetakan memo Anda akan memakai logo default.');
    }
}