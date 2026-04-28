@extends('layouts.app')

@section('title', $article['title'] . ' | Blog MediaTools')
@section('meta_description', $article['description'])
@section('meta_keywords', $article['keywords'])
@section('og_image', ltrim($article['og_image'], '/images/og/'))

@push('schema')
@php
$appUrl = rtrim(config('app.url','https://mediatools.cloud'),'/');
$schema = [
    '@context'         => 'https://schema.org',
    '@type'            => 'Article',
    'headline'         => $article['title'],
    'description'      => $article['description'],
    'image'            => $appUrl . $article['og_image'],
    'author'           => ['@type' => 'Organization', 'name' => 'Tim MediaTools', 'url' => $appUrl],
    'publisher'        => ['@id' => $appUrl . '/#organization'],
    'datePublished'    => $article['date'],
    'dateModified'     => $article['date'],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $appUrl . '/blog/' . $article['slug']],
    'keywords'         => $article['keywords'],
    'inLanguage'       => 'id-ID',
];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<style>
.article-wrap { max-width: 780px; margin: 0 auto; padding: 40px 24px 80px; }
.article-header { margin-bottom: 36px; }
.article-cat {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
    text-transform: uppercase; color: var(--accent);
    background: rgba(163,230,53,0.1); border: 1px solid rgba(163,230,53,0.2);
    padding: 4px 12px; border-radius: 99px; margin-bottom: 16px;
}
.article-title {
    font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800;
    line-height: 1.3; letter-spacing: -0.03em;
    color: var(--text-primary); margin-bottom: 16px;
}
.article-meta {
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    font-size: 12px; color: var(--text-muted); margin-bottom: 24px;
}
.article-meta span { display: flex; align-items: center; gap: 5px; }
.article-hero { width: 100%; border-radius: 16px; margin-bottom: 36px; }
.article-body { font-size: 0.95rem; line-height: 1.85; color: var(--text-dim); }
.article-body h2 { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 32px 0 14px; letter-spacing: -0.02em; }
.article-body h3 { font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 24px 0 10px; }
.article-body p { margin-bottom: 16px; }
.article-body ul, .article-body ol { padding-left: 22px; margin-bottom: 16px; }
.article-body li { margin-bottom: 8px; }
.article-body strong { color: var(--text-primary); font-weight: 600; }
.article-body .callout {
    background: rgba(163,230,53,0.06); border: 1px solid rgba(163,230,53,0.2);
    border-radius: 12px; padding: 16px 20px; margin: 24px 0;
    font-size: 0.9rem; color: var(--text-primary);
}
.article-body .callout i { color: var(--accent); margin-right: 8px; }
.article-cta {
    background: linear-gradient(135deg, var(--card-bg), var(--secondary-bg));
    border: 1px solid var(--border-accent); border-radius: 20px;
    padding: 32px; text-align: center; margin: 40px 0;
}
.article-cta h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; }
.article-cta p { font-size: 0.875rem; color: var(--text-dim); margin-bottom: 20px; }
.article-related { margin-top: 56px; border-top: 1px solid var(--border); padding-top: 40px; }
.article-related h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; }
.article-related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
.article-related-card {
    background: var(--card-bg); border: 1px solid var(--border);
    border-radius: 14px; padding: 16px; text-decoration: none;
    color: inherit; transition: all 0.25s; display: block;
}
.article-related-card:hover { border-color: var(--border-accent); transform: translateY(-2px); }
.article-related-card img { width: 100%; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
.article-related-title { font-size: 0.85rem; font-weight: 600; line-height: 1.4; color: var(--text-primary); }
.breadcrumb-nav { margin-bottom: 24px; }
.breadcrumb-nav ol { display: flex; align-items: center; gap: 8px; list-style: none; padding: 0; }
.breadcrumb-nav ol li { font-size: 12px; color: var(--text-dim); }
.breadcrumb-nav ol li a { color: var(--text-dim); text-decoration: none; }
.breadcrumb-nav ol li a:hover { color: var(--accent); }
</style>

<div class="article-wrap">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <ol>
            <li><a href="{{ url('/') }}">Home</a></li>
            <li>›</li>
            <li><a href="{{ route('blog.index') }}">Blog</a></li>
            <li>›</li>
            <li style="color:var(--accent);font-weight:600;">{{ $article['category'] }}</li>
        </ol>
    </nav>

    <header class="article-header">
        <div class="article-cat">
            <i class="fa-solid fa-tag" style="font-size:9px;"></i>
            {{ $article['category'] }}
        </div>
        <h1 class="article-title">{{ $article['title'] }}</h1>
        <div class="article-meta">
            <span><i class="fa-solid fa-user"></i> {{ $article['author'] }}</span>
            <span><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($article['date'])->translatedFormat('d F Y') }}</span>
            <span><i class="fa-regular fa-clock"></i> {{ $article['read_time'] }} baca</span>
        </div>
        <img src="{{ asset($article['og_image']) }}"
             alt="{{ $article['title'] }}"
             class="article-hero"
             width="780" height="410">
    </header>

    <div class="article-body">

        {{-- ─── KONTEN PER ARTIKEL ─── --}}
        @if($article['content_key'] === 'bg_tutorial')

        <p>Menghapus background foto dulu hanya bisa dilakukan oleh desainer grafis yang paham Photoshop. Tapi sekarang, dengan teknologi AI, siapapun bisa mendapatkan hasil yang sama profesionalnya — dalam hitungan detik, tanpa biaya, tanpa harus menginstal software apapun.</p>

        <h2>Mengapa Harus Hapus Background Foto?</h2>
        <p>Ada banyak situasi di mana Anda butuh foto dengan background transparan atau polos:</p>
        <ul>
            <li><strong>Foto produk e-commerce</strong> — Marketplace seperti Tokopedia dan Shopee mewajibkan foto produk berlatar putih. Foto dengan background transparan bisa dipasang di atas warna apapun.</li>
            <li><strong>Foto profil profesional</strong> — Background kantor yang berantakan? Hapus saja dan ganti dengan warna solid yang rapi.</li>
            <li><strong>Pas foto KTP, SKCK, atau Visa</strong> — Perlu background merah atau biru? Bisa langsung dibuat tanpa perlu ke studio foto.</li>
            <li><strong>Materi desain dan konten</strong> — Stiker, thumbnail YouTube, dan materi marketing membutuhkan objek tanpa background.</li>
        </ul>

        <h2>Cara Hapus Background Foto di MediaTools</h2>
        <p>Berikut langkah-langkah lengkapnya:</p>
        <ol>
            <li><strong>Buka halaman Background Remover</strong> — Kunjungi mediatools.cloud/bg di browser HP atau laptop Anda.</li>
            <li><strong>Upload foto</strong> — Klik area upload atau drag & drop foto dari perangkat Anda. Format yang didukung: JPG, PNG, dan WebP hingga 20MB.</li>
            <li><strong>Tunggu AI bekerja</strong> — Teknologi AI BiRefNet kami akan memproses foto secara otomatis. Biasanya selesai dalam 5-15 detik tergantung ukuran foto.</li>
            <li><strong>Preview dan edit jika perlu</strong> — Lihat hasilnya. Jika ada area yang kurang sempurna (misalnya rambut atau tepi objek), gunakan brush manual untuk merapikan.</li>
            <li><strong>Pilih background atau download</strong> — Download sebagai PNG transparan, atau pilih background warna (putih, merah, biru, hijau) untuk keperluan pas foto.</li>
        </ol>

        <div class="callout">
            <i class="fa-solid fa-lightbulb"></i>
            <strong>Tips:</strong> Untuk hasil terbaik, gunakan foto dengan pencahayaan yang baik dan kontras antara objek dan background asli. Foto dengan background polos (dinding putih, langit biru) menghasilkan hasil AI yang lebih akurat.
        </div>

        <h2>Perbedaan AI BiRefNet vs Tools Lain</h2>
        <p>Sebagian besar background remover gratis menggunakan algoritma GrabCut atau model AI yang lebih lama. Hasilnya sering kasar di bagian rambut dan detail halus. MediaTools menggunakan <strong>BiRefNet</strong> — model AI generasi terbaru yang secara khusus dioptimasi untuk:</p>
        <ul>
            <li>Detail rambut dan bulu yang sangat halus</li>
            <li>Tepi objek yang kompleks (tanaman, jaring, kain tipis)</li>
            <li>Objek semi-transparan</li>
            <li>Foto dengan pencahayaan rendah atau kontras rendah</li>
        </ul>

        <h2>Membuat Pas Foto 2×3, 3×4, dan 4×6 Gratis</h2>
        <p>Fitur bonus yang tidak dimiliki background remover lain: setelah menghapus background, Anda bisa langsung membuat pas foto dengan ukuran standar Indonesia. Pilih mode "Pas Foto", tentukan ukuran, pilih warna background (merah/biru/hijau/putih), dan download sebagai JPG atau PDF A4 siap cetak — tanpa perlu ke studio foto.</p>

        @elseif($article['content_key'] === 'invoice_tutorial')

        <p>Sebagai freelancer, salah satu hal yang paling menentukan apakah klien membayar tepat waktu adalah <strong>invoice yang terlihat profesional</strong>. Invoice yang rapi dan lengkap bukan sekadar estetika — ini mencerminkan profesionalisme Anda dan membuat klien merasa lebih percaya untuk membayar lebih cepat.</p>

        <h2>Elemen Wajib dalam Invoice Profesional</h2>
        <p>Sebelum membuat invoice, pastikan semua elemen berikut ada:</p>
        <ul>
            <li><strong>Nomor Invoice</strong> — Format konsisten seperti INV/2026/04/001 memudahkan Anda tracking pembayaran.</li>
            <li><strong>Tanggal Invoice & Jatuh Tempo</strong> — Selalu cantumkan deadline pembayaran, misalnya "7 hari setelah tanggal invoice".</li>
            <li><strong>Identitas Anda</strong> — Nama/nama usaha, alamat, nomor HP, dan email.</li>
            <li><strong>Identitas Klien</strong> — Nama perusahaan/klien, alamat, dan contact person.</li>
            <li><strong>Rincian Pekerjaan</strong> — Deskripsi jelas setiap item, jumlah, harga satuan, dan total.</li>
            <li><strong>PPN (jika berlaku)</strong> — Jika Anda PKP, cantumkan PPN 11%.</li>
            <li><strong>Informasi Rekening</strong> — Nama bank, nomor rekening, dan atas nama.</li>
        </ul>

        <h2>Cara Buat Invoice di MediaTools</h2>
        <ol>
            <li><strong>Pilih Template</strong> — Tersedia 3 template modern: Klasik (formal), Modern (minimalis), dan Elegan (premium). Pilih sesuai karakter bisnis Anda.</li>
            <li><strong>Upload Logo</strong> — Klik area logo dan upload gambar logo perusahaan/brand Anda.</li>
            <li><strong>Isi Informasi Pihak</strong> — Lengkapi data perusahaan Anda dan data klien.</li>
            <li><strong>Tambah Item Pekerjaan</strong> — Klik "+ Tambah Item" untuk setiap layanan. Masukkan nama, deskripsi, jumlah, dan harga satuan.</li>
            <li><strong>Atur PPN dan Diskon</strong> — Jika ada, masukkan persentase PPN dan diskon. Total akan dihitung otomatis.</li>
            <li><strong>Preview dan Download PDF</strong> — Lihat preview real-time, lalu klik "Download PDF" untuk mendapatkan file siap kirim ke klien.</li>
        </ol>

        <div class="callout">
            <i class="fa-solid fa-lightbulb"></i>
            <strong>Tips Agar Cepat Dibayar:</strong> Selalu cantumkan nomor WhatsApp di invoice agar klien bisa konfirmasi pembayaran langsung. Tambahkan kalimat "Terima kasih atas kepercayaan Anda" — invoice yang sopan cenderung dibayar lebih cepat daripada yang terkesan menuntut.
        </div>

        <h2>Format Penomoran Invoice yang Profesional</h2>
        <p>Gunakan sistem penomoran yang konsisten: <code>INV/YYYY/MM/XXX</code>. Contoh: INV/2026/04/001. Format ini memudahkan Anda melacak invoice per tahun dan bulan, dan terlihat lebih profesional daripada nomor acak.</p>

        @elseif($article['content_key'] === 'tiktok_tutorial')

        <p>Ingin menyimpan video TikTok favorit — entah itu dance challenge, resep masakan, atau tutorial — tapi tidak mau ada watermark logo TikTok yang mengganggu? Anda bisa melakukannya secara gratis menggunakan MediaTools, tanpa perlu instal aplikasi apapun.</p>

        <h2>Kenapa Video TikTok Punya Watermark?</h2>
        <p>TikTok secara default menambahkan watermark (logo + username) pada semua video yang didownload lewat tombol "Simpan Video" bawaan aplikasi. Tujuannya untuk mencegah penyebaran konten tanpa kredit ke pembuat. Namun untuk penggunaan personal — menyimpan resep, tutorial, atau konten edukatif untuk ditonton offline — watermark ini seringkali mengganggu.</p>

        <div class="callout">
            <i class="fa-solid fa-circle-exclamation"></i>
            <strong>Penting:</strong> Selalu hormati hak cipta kreator. Jangan gunakan konten orang lain untuk keperluan komersial tanpa izin. Download hanya untuk keperluan pribadi.
        </div>

        <h2>Cara Download Video TikTok Tanpa Watermark</h2>
        <ol>
            <li><strong>Salin link video TikTok</strong> — Buka aplikasi TikTok, tap tombol Share → "Salin Tautan".</li>
            <li><strong>Buka Media Downloader</strong> — Kunjungi mediatools.cloud/media-downloader di browser HP atau laptop.</li>
            <li><strong>Pilih tab TikTok</strong> — Tap/klik tab "TikTok" di bagian platform.</li>
            <li><strong>Paste URL</strong> — Tempel link yang sudah disalin di kolom URL.</li>
            <li><strong>Aktifkan "Tanpa Watermark"</strong> — Pastikan opsi tanpa watermark aktif.</li>
            <li><strong>Klik Download</strong> — Tunggu beberapa detik, lalu simpan video ke perangkat Anda.</li>
        </ol>

        <h2>Bisa Download dari Platform Lain Juga?</h2>
        <p>Ya! Media Downloader MediaTools mendukung 20+ platform. Yang paling populer:</p>
        <ul>
            <li><strong>YouTube</strong> — Download MP4 hingga 1080p atau konversi ke MP3</li>
            <li><strong>Instagram</strong> — Reels, foto, dan video profil</li>
            <li><strong>Facebook</strong> — Video publik</li>
            <li><strong>Twitter/X</strong> — Video tweet</li>
        </ul>

        @elseif($article['content_key'] === 'pdf_tutorial')

        <p>File PDF yang besar jadi masalah saat ingin kirim lewat email (batas 25MB), WhatsApp (batas 100MB untuk dokumen), atau upload ke portal CPNS, SPAN-PTKIN, atau sistem e-procurement pemerintah yang sering membatasi ukuran file. Solusinya: kompres PDF tanpa kehilangan kualitas yang berarti.</p>

        <h2>Berapa Ukuran PDF yang Ideal?</h2>
        <ul>
            <li><strong>Email attachment</strong> — Di bawah 5MB ideal, maksimal 10MB</li>
            <li><strong>WhatsApp</strong> — Di bawah 16MB untuk pengiriman lancar</li>
            <li><strong>Portal lamaran kerja / CPNS</strong> — Biasanya dibatasi 1-2MB</li>
            <li><strong>Presentasi klien</strong> — Di bawah 10MB untuk mudah dibagikan</li>
        </ul>

        <h2>Cara Kompres PDF di MediaTools</h2>
        <ol>
            <li><strong>Buka PDF Utilities</strong> — Kunjungi mediatools.cloud/pdfutilities.</li>
            <li><strong>Upload file PDF</strong> — Drag & drop atau klik untuk pilih file. Maksimal 50MB per file.</li>
            <li><strong>Pilih fitur Compress</strong> — Klik tab atau tombol "Compress PDF".</li>
            <li><strong>Pilih level kompresi</strong> — "Kualitas Tinggi" (kompresi ringan), "Seimbang" (rekomendasi), atau "Ukuran Terkecil" (untuk portal dengan limit ketat).</li>
            <li><strong>Proses dan Download</strong> — Klik Compress, tunggu beberapa detik, lalu download PDF yang sudah dikompres.</li>
        </ol>

        <div class="callout">
            <i class="fa-solid fa-lightbulb"></i>
            <strong>Tips:</strong> Jika PDF Anda berisi banyak gambar (foto produk, scan dokumen), level kompresi "Seimbang" biasanya menghasilkan pengurangan ukuran 40-70% dengan kualitas yang masih sangat baik untuk layar dan print standar.
        </div>

        <h2>Fitur PDF Lainnya yang Tersedia</h2>
        <p>Selain compress, PDF Utilities MediaTools juga menyediakan:</p>
        <ul>
            <li><strong>Merge PDF</strong> — Gabungkan beberapa file PDF menjadi satu</li>
            <li><strong>Split PDF</strong> — Pisahkan halaman tertentu dari dokumen besar</li>
            <li><strong>Rotate PDF</strong> — Putar halaman yang orientasinya terbalik</li>
        </ul>

        @elseif($article['content_key'] === 'pdf_word_tutorial')

        <p>Menerima kontrak, laporan, atau dokumen penting dalam format PDF yang perlu diedit? Masalah umum yang sering dihadapi: PDF tidak bisa langsung diedit seperti Word. Solusinya adalah mengkonversi PDF ke format Word (.docx) terlebih dahulu.</p>

        <h2>Kapan Perlu Convert PDF ke Word?</h2>
        <ul>
            <li>Menerima draft kontrak dari klien yang perlu direvisi</li>
            <li>Laporan PDF yang datanya perlu dipindah ke Excel</li>
            <li>Template PDF dari internet yang ingin dimodifikasi</li>
            <li>Dokumen scan yang perlu diedit teksnya</li>
            <li>Mengubah CV lama dalam format PDF</li>
        </ul>

        <h2>Cara Convert PDF ke Word di MediaTools</h2>
        <ol>
            <li><strong>Buka File Converter</strong> — Kunjungi mediatools.cloud/file-converter.</li>
            <li><strong>Upload file PDF</strong> — Drag & drop atau klik untuk pilih. Maksimal 50MB.</li>
            <li><strong>Pilih output format</strong> — Pilih "Word (.docx)" sebagai format tujuan.</li>
            <li><strong>Klik Convert</strong> — Sistem akan memproses konversi. Biasanya selesai dalam 10-30 detik.</li>
            <li><strong>Download hasil</strong> — File Word siap diedit akan otomatis terunduh.</li>
        </ol>

        <div class="callout">
            <i class="fa-solid fa-lightbulb"></i>
            <strong>Catatan tentang PDF scan:</strong> PDF yang berasal dari hasil scan (bukan PDF digital murni) dikonversi menggunakan OCR. Kualitas hasil sangat tergantung kejernihan scan. Scan dengan resolusi 300 DPI ke atas menghasilkan teks yang paling akurat.
        </div>

        <h2>Tips Setelah Konversi</h2>
        <p>Hasil konversi PDF ke Word umumnya sangat baik, namun dokumen dengan layout kompleks (multi-kolom, tabel rumit, gambar yang banyak) mungkin perlu sedikit penyesuaian:</p>
        <ul>
            <li>Periksa tabel — pastikan kolom dan baris terstruktur dengan benar</li>
            <li>Cek gambar — gambar mungkin perlu diposisikan ulang</li>
            <li>Verifikasi font — font khusus mungkin terganti dengan font standar</li>
        </ul>

        @endif

    </div>{{-- /.article-body --}}

    {{-- CTA ke tool --}}
    <div class="article-cta">
        <h3>Coba {{ $article['tool_name'] }} Sekarang — Gratis</h3>
        <p>Tidak perlu daftar akun. Langsung pakai, langsung hasilnya.</p>
        <a href="{{ $article['tool_url'] }}" class="btn-primary" style="display:inline-flex;padding:12px 28px;font-size:0.9rem;">
            <i class="fa-solid fa-arrow-right-to-bracket" style="font-size:0.8rem;"></i>
            Buka {{ $article['tool_name'] }}
        </a>
    </div>

    {{-- Related articles --}}
    @if(count($related))
    <div class="article-related">
        <h2>Artikel Terkait</h2>
        <div class="article-related-grid">
            @foreach($related as $rel)
            <a href="{{ route('blog.show', $rel['slug']) }}" class="article-related-card">
                <img src="{{ asset($rel['og_image']) }}" alt="{{ $rel['title'] }}" loading="lazy">
                <div class="article-related-title">{{ $rel['title'] }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
