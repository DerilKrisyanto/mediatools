<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\MemoPengiriman;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemoPengirimanController extends Controller
{
    /**
     * Tampilkan form input + rekap memo milik user yang sedang login.
     */
    public function index(): View
    {
        $memos = MemoPengiriman::milikSaya()
            ->latest('tanggal_memo')
            ->latest('id')
            ->paginate(10);

        return view('tools.memopengiriman.index', compact('memos'));
    }

    /**
     * Tampilkan form edit (memakai view yang sama dengan index).
     */
    public function edit(MemoPengiriman $memoPengiriman): View
    {
        $this->authorizeOwner($memoPengiriman);

        $memos = MemoPengiriman::milikSaya()
            ->latest('tanggal_memo')
            ->latest('id')
            ->paginate(10);

        return view('tools.memopengiriman.index', [
            'memos'    => $memos,
            'editMemo' => $memoPengiriman,
        ]);
    }

    /**
     * Simpan memo baru untuk user yang sedang login.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['instalasi']  = $request->boolean('instalasi');
        $validated['user_id']    = Auth::id();
        $validated['nomor_memo'] = $this->generateNomorMemo();

        MemoPengiriman::create($validated);

        return redirect()
            ->route('tools.memopengiriman')
            ->with('success', 'Memo pengiriman berhasil disimpan.');
    }

    /**
     * Update memo — hanya boleh oleh pemilik data.
     */
    public function update(Request $request, MemoPengiriman $memoPengiriman): RedirectResponse
    {
        $this->authorizeOwner($memoPengiriman);

        $validated = $request->validate($this->rules());
        $validated['instalasi'] = $request->boolean('instalasi');

        $memoPengiriman->update($validated);

        return redirect()
            ->route('tools.memopengiriman')
            ->with('success', 'Memo pengiriman berhasil diperbarui.');
    }

    /**
     * Hapus 1 memo — hanya boleh oleh pemilik data.
     */
    public function destroy(MemoPengiriman $memoPengiriman): RedirectResponse
    {
        $this->authorizeOwner($memoPengiriman);
        $memoPengiriman->delete();

        return redirect()
            ->route('tools.memopengiriman')
            ->with('success', 'Memo pengiriman berhasil dihapus.');
    }

    /**
     * Cetak 1 memo menjadi PDF (setengah halaman A4).
     * Logo yang tampil otomatis mengikuti logo milik user yang login.
     */
    public function pdf(MemoPengiriman $memoPengiriman)
    {
        $this->authorizeOwner($memoPengiriman);

        $pdf = Pdf::loadView('tools.memopengiriman.pdf', [
            'memos'    => collect([$memoPengiriman]),
            'logoPath' => $this->resolveLogoPath(Auth::user()),
        ])->setPaper('a4', 'portrait');

        $namaFile = $this->sanitizeFilename('memo-pengiriman-' . $memoPengiriman->nomor_memo) . '.pdf';

        return $pdf->stream($namaFile);
    }

    /**
     * Cetak beberapa memo TERPILIH sekaligus dalam 1 file PDF.
     * 2 memo per halaman fisik (masing-masing setengah A4).
     * Query otomatis dibatasi milik user login lewat scope milikSaya().
     */
    public function bulkPdf(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $memos = MemoPengiriman::milikSaya()
            ->whereIn('id', $request->input('ids'))
            ->orderByDesc('tanggal_memo')
            ->orderByDesc('id')
            ->get();

        abort_if($memos->isEmpty(), 404, 'Tidak ada memo yang dapat dicetak.');

        $pdf = Pdf::loadView('tools.memopengiriman.pdf', [
            'memos'    => $memos,
            'logoPath' => $this->resolveLogoPath(Auth::user()),
        ])->setPaper('a4', 'portrait');

        $namaFile = 'memo-pengiriman-terpilih-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->stream($namaFile);
    }

    /**
     * Hapus beberapa memo TERPILIH sekaligus.
     * Query otomatis dibatasi milik user login — aman dari manipulasi ID orang lain.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $jumlah = MemoPengiriman::milikSaya()
            ->whereIn('id', $request->input('ids'))
            ->delete();

        return redirect()
            ->route('tools.memopengiriman')
            ->with('success', "{$jumlah} memo pengiriman berhasil dihapus.");
    }

    /**
     * Pastikan memo yang diakses benar-benar milik user yang sedang login.
     */
    private function authorizeOwner(MemoPengiriman $memo): void
    {
        abort_if($memo->user_id !== Auth::id(), 403, 'Anda tidak memiliki akses ke memo ini.');
    }

    /**
     * Tentukan path fisik logo yang dipakai di PDF:
     * - Kalau user punya logo sendiri & filenya benar-benar ada di storage → pakai itu.
     * - Kalau tidak → fallback ke logo default sistem.
     */
    private function resolveLogoPath(User $user): string
    {
        if ($user->logo_path) {
            $fullPath = storage_path('app/public/' . $user->logo_path);
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        return public_path('images/logo-ahi.jpg');
    }

    /**
     * Buat nomor memo otomatis, format: MEMO/YYYYMMDD/0001
     */
    private function generateNomorMemo(): string
    {
        $prefix = 'MEMO/' . now()->format('Ymd') . '/';

        $urutanHariIni = MemoPengiriman::whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix . str_pad((string) $urutanHariIni, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Bersihkan string agar aman dipakai sebagai nama file (menghapus "/" dan "\").
     */
    private function sanitizeFilename(string $value): string
    {
        return str_replace(['/', '\\'], '-', $value);
    }

    /**
     * Rules validasi untuk store & update.
     *
     * Catatan:
     * - no_struk wajib diisi (bagian "Telah Terima Dari" selalu tampil).
     * - no_struk_instalasi & instalasi_hari_tanggal hanya WAJIB kalau instalasi = Ya (required_if),
     *   karena di form kedua field itu disembunyikan saat instalasi = Tidak.
     */
    private function rules(): array
    {
        return [
            'tanggal_memo'             => ['required', 'date'],
            'diterima_dari'            => ['required', 'string', 'max:150'],
            'no_struk'                 => ['required', 'string', 'max:100'],
            'telepon_dari'             => ['nullable', 'regex:/^[0-9+\-\s]{0,30}$/'],
            'berupa'                   => ['required', 'string'],
            'tujuan_contact_person'    => ['required', 'string', 'max:150'],
            'tujuan_alamat'            => ['required', 'string'],
            'tujuan_telepon'           => ['nullable', 'regex:/^[0-9+\-\s]{0,30}$/'],
            'pengiriman_hari_tanggal'  => ['nullable', 'string', 'max:100'],
            'biaya_kirim'              => ['nullable', 'numeric', 'min:0'],
            'instalasi'                => ['nullable', 'boolean'],
            'no_struk_instalasi'       => ['nullable', 'required_if:instalasi,1', 'string', 'max:100'],
            'instalasi_hari_tanggal'   => ['nullable', 'required_if:instalasi,1', 'string', 'max:100'],
            'biaya_instalasi'          => ['nullable', 'numeric', 'min:0'],
        ];
    }
}