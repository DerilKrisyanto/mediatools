<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Mail\MemoPengirimanMail;
use App\Models\MemoPengiriman;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\MemoPengirimanExport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MemoPengirimanController extends Controller
{
    public function index(Request $request): View
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        // 1. Ambil query dasar, filter periode berdasarkan pengiriman_hari_tanggal
        $query = MemoPengiriman::milikSaya();
        $this->applyPengirimanDateFilter($query, $dateFrom, $dateTo);

        // 2. Urutkan berdasarkan pengiriman_hari_tanggal (bukan tanggal_memo),
        //    memakai ekspresi yang sama dengan filter di atas agar konsisten
        //    dan otomatis mendukung PostgreSQL maupun MySQL.
        $query->orderByRaw($this->pengirimanDateExpression() . ' DESC');

        // 3. Tambahkan secondary order dan pagination
        $memos = $query->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('tools.memopengiriman.index', compact('memos', 'dateFrom', 'dateTo'));
    }

    public function edit(Request $request, MemoPengiriman $memoPengiriman): View
    {
        $this->authorizeOwner($memoPengiriman);

        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $query = MemoPengiriman::milikSaya();
        $this->applyPengirimanDateFilter($query, $dateFrom, $dateTo);

        $memos = $query->orderByRaw($this->pengirimanDateExpression() . ' DESC')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('tools.memopengiriman.index', [
            'memos'    => $memos,
            'editMemo' => $memoPengiriman,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
        ]);
    }

    /**
     * Simpan memo baru untuk user yang sedang login.
     * No. Struk & No. Struk Instalasi dikirim sebagai array (items),
     * lalu digabung jadi 1 string dipisah koma sebelum disimpan
     * ke kolom no_struk / no_struk_instalasi yang sudah ada.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $instalasi = $request->boolean('instalasi');
        $noStruk   = $this->joinList($request->input('no_struk_items', []));

        if ($noStruk === '') {
            return back()
                ->withErrors(['no_struk_items.0' => 'No. Struk wajib diisi minimal 1.'])
                ->withInput();
        }

        $noStrukInstalasi = $instalasi
            ? $this->joinList($request->input('no_struk_instalasi_items', []))
            : '';

        if ($instalasi && $noStrukInstalasi === '') {
            return back()
                ->withErrors(['no_struk_instalasi_items.0' => 'No. Struk Instalasi wajib diisi minimal 1 jika Instalasi dipilih Ya.'])
                ->withInput();
        }

        $barangItems = $this->buildBarangItems($request);

        if (empty($barangItems)) {
            return back()
                ->withErrors(['barang_nama.0' => 'Minimal isi 1 nama barang.'])
                ->withInput();
        }

        unset(
            $validated['no_struk_items'],
            $validated['no_struk_instalasi_items'],
            $validated['barang_nama'],
            $validated['barang_qty']
        );

        $validated['instalasi']          = $instalasi;
        $validated['no_struk']           = $noStruk;
        $validated['no_struk_instalasi'] = $noStrukInstalasi ?: null;
        $validated['berupa']             = $barangItems;
        $validated['user_id']            = Auth::id();
        $validated['nomor_memo']         = $this->generateNomorMemo();

        MemoPengiriman::create($validated);

        return redirect()
            ->route('tools.memopengiriman')
            ->with('success', 'Memo pengiriman berhasil disimpan.');
    }

    /**
     * Update memo — hanya boleh oleh pemilik data.
     * Logic penggabungan No. Struk sama seperti store().
     */
    public function update(Request $request, MemoPengiriman $memoPengiriman): RedirectResponse
    {
        $this->authorizeOwner($memoPengiriman);

        $validated = $request->validate($this->rules());

        $instalasi = $request->boolean('instalasi');
        $noStruk   = $this->joinList($request->input('no_struk_items', []));

        if ($noStruk === '') {
            return back()
                ->withErrors(['no_struk_items.0' => 'No. Struk wajib diisi minimal 1.'])
                ->withInput();
        }

        $noStrukInstalasi = $instalasi
            ? $this->joinList($request->input('no_struk_instalasi_items', []))
            : '';

        if ($instalasi && $noStrukInstalasi === '') {
            return back()
                ->withErrors(['no_struk_instalasi_items.0' => 'No. Struk Instalasi wajib diisi minimal 1 jika Instalasi dipilih Ya.'])
                ->withInput();
        }

        $barangItems = $this->buildBarangItems($request);

        if (empty($barangItems)) {
            return back()
                ->withErrors(['barang_nama.0' => 'Minimal isi 1 nama barang.'])
                ->withInput();
        }

        unset(
            $validated['no_struk_items'],
            $validated['no_struk_instalasi_items'],
            $validated['barang_nama'],
            $validated['barang_qty']
        );

        $validated['instalasi']          = $instalasi;
        $validated['no_struk']           = $noStruk;
        $validated['no_struk_instalasi'] = $noStrukInstalasi ?: null;
        $validated['berupa']             = $barangItems;

        $memoPengiriman->update($validated);

        return redirect()
            ->route('tools.memopengiriman')
            ->with('success', 'Memo pengiriman berhasil diperbarui.');
    }

    public function destroy(MemoPengiriman $memoPengiriman): RedirectResponse
    {
        $this->authorizeOwner($memoPengiriman);
        $memoPengiriman->delete();

        return redirect()
            ->route('tools.memopengiriman')
            ->with('success', 'Memo pengiriman berhasil dihapus.');
    }

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

    public function exportExcel(Request $request): StreamedResponse
    {
        $request->validate([
            'ids'       => ['nullable', 'array'],
            'ids.*'     => ['integer'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
        ]);

        $ids = array_filter($request->input('ids', []));

        // 1. Inisialisasi Query Dasar
        $query = MemoPengiriman::milikSaya();

        if (!empty($ids)) {
            // Jika user memilih baris data tertentu via checkbox
            $query->whereIn('id', $ids);
        } else {
            // Jika user melakukan export berdasarkan filter kalender —
            // filter periode berdasarkan pengiriman_hari_tanggal (bukan tanggal_memo)
            $dateFrom = $request->input('date_from') ?: now()->toDateString();
            $dateTo   = $request->input('date_to') ?: now()->toDateString();

            $this->applyPengirimanDateFilter($query, $dateFrom, $dateTo);
        }

        // 2. Urutkan berdasarkan pengiriman_hari_tanggal, konsisten dengan index()/edit()
        //    dan otomatis mendukung PostgreSQL maupun MySQL.
        $query->orderByRaw($this->pengirimanDateExpression() . ' DESC');

        // 3. Eksekusi Pengurutan Cadangan & Get Data
        $memos = $query->orderByDesc('id')->get();

        abort_if($memos->isEmpty(), 404, 'Tidak ada data untuk diekspor.');

        return $this->buildExcelResponse($memos);
    }

    /**
     * Kirim hasil cetak PDF dari 1 memo ke email tujuan (kolom email_tujuan_person),
     * memakai mail sender bawaan sistem (Resend, sama seperti pengiriman OTP).
     * Hanya berlaku untuk 1 data — tidak ada versi bulk untuk aksi ini.
     */
    public function kirimEmail(MemoPengiriman $memoPengiriman): RedirectResponse
    {
        $this->authorizeOwner($memoPengiriman);

        if (empty($memoPengiriman->email_tujuan_person)) {
            return redirect()
                ->route('tools.memopengiriman')
                ->withErrors([
                    'kirim' => 'Email tujuan untuk memo "' . $memoPengiriman->nomor_memo . '" belum diisi. Silakan edit memo ini dan isi kolom "Email Tujuan" terlebih dahulu sebelum mengirim.',
                ]);
        }

        try {
            $pdfBinary = Pdf::loadView('tools.memopengiriman.pdf', [
                'memos'    => collect([$memoPengiriman]),
                'logoPath' => $this->resolveLogoPath(Auth::user()),
            ])->setPaper('a4', 'portrait')->output();

            $namaFile = $this->sanitizeFilename('memo-pengiriman-' . $memoPengiriman->nomor_memo) . '.pdf';

            Mail::to($memoPengiriman->email_tujuan_person)
                ->send(new MemoPengirimanMail($memoPengiriman, $pdfBinary, $namaFile));

        } catch (\Exception $e) {
            Log::error('Gagal mengirim email memo pengiriman', [
                'memo_id' => $memoPengiriman->id,
                'email'   => $memoPengiriman->email_tujuan_person,
                'error'   => $e->getMessage(),
            ]);

            return redirect()
                ->route('tools.memopengiriman')
                ->withErrors([
                    'kirim' => 'Gagal mengirim email ke ' . $memoPengiriman->email_tujuan_person . '. Pastikan alamat email tersebut benar dan aktif, lalu coba lagi. Jika masalah berlanjut, hubungi support.',
                ]);
        }

        return redirect()
            ->route('tools.memopengiriman')
            ->with('success', 'Cetakan PDF memo "' . $memoPengiriman->nomor_memo . '" berhasil dikirim ke ' . $memoPengiriman->email_tujuan_person . '.');
    }

    private function authorizeOwner(MemoPengiriman $memo): void
    {
        abort_if($memo->user_id !== Auth::id(), 403, 'Anda tidak memiliki akses ke memo ini.');
    }

    private function resolveDateRange(Request $request): array
    {
        $today = now()->toDateString();

        $dateFrom = $request->query('date_from', $today);
        $dateTo   = $request->query('date_to', $today);

        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        return [$dateFrom, $dateTo];
    }

    /**
     * Bangun ekspresi SQL mentah yang mem-parsing kolom pengiriman_hari_tanggal
     * (contoh isi kolom: "Minggu, 05-Juli-2026 17:37") menjadi nilai timestamp
     * yang bisa dibandingkan/diurutkan secara native oleh database.
     *
     * Dipakai untuk WHERE (filter periode) maupun ORDER BY (urutan tabel & export),
     * supaya keduanya selalu konsisten memakai sumber tanggal yang sama persis.
     * Mendukung PostgreSQL (produksi) maupun MySQL (Laragon lokal).
     *
     * PENTING: fungsi to_timestamp() / STR_TO_DATE() di bawah ini akan error
     * jika ada baris dengan isi pengiriman_hari_tanggal yang formatnya TIDAK
     * sesuai pola "Hari, DD-NamaBulan-YYYY HH:MM" (misalnya diedit manual
     * langsung di database dengan format bebas). Selama field ini hanya diisi
     * lewat datetime-picker di form (seperti sekarang), formatnya akan selalu
     * konsisten dan aman.
     */
    private function pengirimanDateExpression(): string
    {
        // Nama bulan Indonesia yang penulisannya berbeda dari bahasa Inggris.
        // April, September, November tidak perlu diganti karena penulisannya sama.
        $bulanMap = [
            'Januari'  => 'January',
            'Februari' => 'February',
            'Maret'    => 'March',
            'Mei'      => 'May',
            'Juni'     => 'June',
            'Juli'     => 'July',
            'Agustus'  => 'August',
            'Oktober'  => 'October',
            'Desember' => 'December',
        ];

        if (config('database.default') === 'pgsql') {
            // Buang nama hari di depan (ambil mulai dari karakter angka pertama)
            $expr = "substring(pengiriman_hari_tanggal from '[0-9].*')";

            foreach ($bulanMap as $id => $en) {
                $expr = "regexp_replace({$expr}, '{$id}', '{$en}', 'g')";
            }

            return "to_timestamp({$expr}, 'DD-Month-YYYY HH24:MI')";
        }

        // MySQL (Laragon lokal): buang nama hari di depan via posisi koma,
        // lalu ganti nama bulan Indonesia -> Inggris secara berantai.
        $expr = "SUBSTRING(pengiriman_hari_tanggal, LOCATE(', ', pengiriman_hari_tanggal) + 2)";

        foreach ($bulanMap as $id => $en) {
            $expr = "REPLACE({$expr}, '{$id}', '{$en}')";
        }

        return "STR_TO_DATE({$expr}, '%d-%M-%Y %H:%i')";
    }

    /**
     * Terapkan filter rentang tanggal ($dateFrom - $dateTo) berdasarkan kolom
     * pengiriman_hari_tanggal (bukan tanggal_memo), memakai ekspresi parsing
     * di atas supaya string seperti "Minggu, 05-Juli-2026 17:37" bisa
     * dibandingkan sebagai tanggal sesungguhnya.
     *
     * Data dengan pengiriman_hari_tanggal kosong/null otomatis TIDAK akan
     * muncul saat difilter — karena memang tidak ada tanggal pengiriman
     * untuk dicocokkan terhadap rentang yang dipilih.
     */
    private function applyPengirimanDateFilter($query, string $dateFrom, string $dateTo): void
    {
        $expr = $this->pengirimanDateExpression();

        $query->whereNotNull('pengiriman_hari_tanggal')
            ->where('pengiriman_hari_tanggal', '!=', '')
            ->whereRaw("CAST({$expr} AS DATE) BETWEEN ? AND ?", [$dateFrom, $dateTo]);
    }

    private function buildExcelResponse($memos): StreamedResponse
    {
        $filename = 'rekap-memo-pengiriman-' . now()->format('Ymd-His') . '.xlsx';

        $spreadsheet = (new MemoPengirimanExport($memos))->build();
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

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

    private function generateNomorMemo(): string
    {
        $userId = Auth::id();

        $prefix = 'MEMO' . str_pad($userId, 3, '0', STR_PAD_LEFT)
                . '/' . now()->format('Ymd') . '/';

        $urutan = MemoPengiriman::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return $prefix . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    private function sanitizeFilename(string $value): string
    {
        return str_replace(['/', '\\'], '-', $value);
    }

    /**
     * Gabungkan array item (no. struk / no. struk instalasi) jadi 1 string,
     * membuang item kosong/whitespace-only. Hasilnya disimpan ke kolom
     * no_struk / no_struk_instalasi yang sudah ada (tanpa field baru).
     */
    private function joinList(array $items): string
    {
        $clean = array_values(array_filter(
            array_map('trim', $items),
            fn ($v) => $v !== ''
        ));

        return implode(', ', $clean);
    }

    /**
     * Gabungkan barang_nama[] & barang_qty[] jadi array [{nama, qty}, ...],
     * membuang baris yang nama-nya kosong. Qty default 1 kalau kosong/invalid.
     * Hasilnya disimpan ke kolom `berupa` (cast array -> otomatis jadi JSON).
     */
    private function buildBarangItems(Request $request): array
    {
        $namaList = $request->input('barang_nama', []);
        $qtyList  = $request->input('barang_qty', []);

        $items = [];

        foreach ($namaList as $index => $nama) {
            $nama = trim((string) $nama);
            if ($nama === '') {
                continue;
            }

            $qty = (int) ($qtyList[$index] ?? 1);
            $items[] = [
                'nama' => $nama,
                'qty'  => $qty > 0 ? $qty : 1,
            ];
        }

        return $items;
    }

    /**
     * Rules validasi untuk store & update.
     * no_struk & no_struk_instalasi sekarang divalidasi sebagai array item,
     * baru digabung jadi string di dalam method store()/update().
     */
    private function rules(): array
    {
        return [
            'tanggal_memo'                => ['required', 'date'],
            'diterima_dari'               => ['required', 'string', 'max:150'],
            'no_struk_items'              => ['required', 'array', 'min:1'],
            'no_struk_items.*'            => ['required', 'string', 'max:100'],
            'telepon_dari'                => ['nullable', 'regex:/^[0-9+\-\s]{0,30}$/'],
            'barang_nama'                 => ['required', 'array', 'min:1'],
            'barang_nama.*'               => ['required', 'string', 'max:150'],
            'barang_qty'                  => ['nullable', 'array'],
            'barang_qty.*'                => ['nullable', 'integer', 'min:1'],
            'tujuan_contact_person'       => ['required', 'string', 'max:150'],
            'tujuan_alamat'               => ['required', 'string'],
            'keterangan_lainnya'          => ['nullable', 'string'],
            'tujuan_telepon'              => ['nullable', 'regex:/^[0-9+\-\s]{0,30}$/'],
            'email_tujuan_person'         => ['nullable', 'email:rfc,dns', 'max:255'],
            'customer_service'            => ['required', 'string', 'max:150'],
            'nama_sales'                  => ['nullable', 'string', 'max:150'],
            'pengiriman_hari_tanggal'     => ['nullable', 'string', 'max:100'],
            'biaya_kirim'                 => ['nullable', 'numeric', 'min:0'],
            'instalasi'                   => ['nullable', 'boolean'],
            'no_struk_instalasi_items'    => ['nullable', 'array'],
            'no_struk_instalasi_items.*'  => ['nullable', 'string', 'max:100'],
            'instalasi_hari_tanggal'      => ['nullable', 'required_if:instalasi,1', 'string', 'max:100'],
            'biaya_instalasi'             => ['nullable', 'numeric', 'min:0'],
        ];
    }
}