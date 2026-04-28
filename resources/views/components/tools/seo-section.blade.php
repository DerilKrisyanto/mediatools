{{--
    resources/views/components/tools/seo-section.blade.php
    ──────────────────────────────────────────────────────
    Komponen SEO teks yang muncul di BAWAH setiap halaman tool.
    Ini adalah solusi utama agar Google bisa mengindeks konten halaman.

    Cara pakai di blade tool:
        @include('components.tools.seo-section', ['tool' => 'bgremover'])

    Props yang diterima: $tool (string) — nama tool, sesuaikan dengan $toolData keys.
--}}

@php
/**
 * Data konten SEO per tool.
 * Setiap tool punya: description (200+ kata), faq, howto, relatedTools
 */
$toolData = [

    'bgremover' => [
        'title'       => 'Background Remover — Hapus Background Foto Online Gratis',
        'intro'       => 'Background Remover MediaTools menggunakan teknologi AI BiRefNet generasi terbaru untuk menghapus latar belakang foto secara otomatis dalam hitungan detik. Berbeda dengan tools lain yang sering merusak rambut dan tepi objek halus, algoritma BiRefNet kami secara khusus dirancang untuk menangani detail rambut, bulu hewan, dan batas objek yang kompleks dengan presisi tinggi.',
        'desc'        => 'Baik Anda seorang fotografer yang butuh foto produk berlatar transparan, HR yang perlu merapikan foto karyawan, atau content creator yang ingin menganti background sesuai konten — Background Remover ini bekerja langsung di browser tanpa perlu menginstal aplikasi apapun. Upload foto JPG, PNG, atau WebP hingga 20MB, dan AI kami akan memproses hasilnya dalam waktu kurang dari 10 detik. Setelah diproses, Anda bisa mengunduh hasil dalam format PNG transparan berkualitas tinggi, atau langsung pilih background warna solid (putih, merah, biru, hijau) sesuai kebutuhan — termasuk untuk membuat pas foto 2×3, 3×4, dan 4×6 siap cetak.',
        'use_cases'   => ['Foto produk e-commerce', 'Foto profil profesional', 'Pas foto KTP/SKCK/visa', 'Materi desain grafis', 'Konten media sosial', 'Thumbnail video YouTube'],
        'faq'         => [
            ['q' => 'Apakah background remover ini benar-benar gratis?', 'a' => 'Ya, 100% gratis tanpa batas penggunaan harian. Tidak perlu membuat akun atau memasukkan kartu kredit. Langsung upload dan proses.'],
            ['q' => 'Format gambar apa yang didukung?', 'a' => 'Mendukung JPG, PNG, dan WebP hingga ukuran 20MB per file. Hasil output tersedia dalam format PNG transparan berkualitas tinggi.'],
            ['q' => 'Apakah hasilnya akurat untuk foto dengan rambut?', 'a' => 'Ya, AI BiRefNet kami secara khusus dioptimasi untuk menangani detail rambut, bulu, dan tepi objek yang kompleks. Hasilnya jauh lebih rapi dibandingkan tools berbasis GrabCut konvensional.'],
            ['q' => 'Apakah foto saya aman dan privat?', 'a' => 'File diproses di server aman kami dan otomatis dihapus setelah proses selesai. Kami tidak menyimpan atau menggunakan foto Anda untuk keperluan apapun.'],
            ['q' => 'Bisakah saya membuat pas foto 2x3 atau 3x4?', 'a' => 'Bisa. Pilih mode "Pas Foto" di halaman Background Remover, upload foto wajah, pilih ukuran (2×3, 3×4, atau 4×6), dan pilih warna background. Hasilnya bisa didownload sebagai PNG atau PDF A4 siap cetak.'],
        ],
        'relatedTools' => [
            ['name' => 'Image Converter', 'desc' => 'Resize & compress gambar', 'url' => '/imageconverter', 'icon' => 'fa-image'],
            ['name' => 'File Security Scanner', 'desc' => 'Hapus metadata foto', 'url' => '/sanitizer', 'icon' => 'fa-shield-halved'],
            ['name' => 'PDF Utilities', 'desc' => 'Gabung foto ke PDF', 'url' => '/pdfutilities', 'icon' => 'fa-file-pdf'],
        ],
    ],

    'invoice' => [
        'title'       => 'Invoice Generator — Buat Tagihan PDF Profesional Gratis',
        'intro'       => 'Invoice Generator MediaTools memudahkan freelancer, UMKM, dan profesional Indonesia untuk membuat tagihan (invoice) yang terlihat profesional dalam waktu kurang dari 2 menit — tanpa perlu software akuntansi berbayar atau skill desain grafis.',
        'desc'        => 'Tersedia 3 template invoice modern (Klasik, Modern, Elegan) yang bisa dikustomisasi penuh: logo perusahaan, informasi klien, daftar item jasa/produk, kalkulasi PPN/pajak otomatis, diskon, dan rekening pembayaran. Nomor invoice dibuat otomatis dan bisa disesuaikan. Semua hasil bisa diunduh langsung sebagai PDF siap kirim ke klien — tanpa watermark, tanpa biaya. Cocok untuk invoice jasa desain, proyek IT, konsultasi, penjualan produk, atau tagihan apapun yang Anda butuhkan.',
        'use_cases'   => ['Invoice freelancer jasa desain/IT', 'Tagihan UMKM produk fisik', 'Invoice jasa konsultasi', 'Nota penjualan online', 'Faktur proyek konstruksi', 'Tagihan langganan bulanan'],
        'faq'         => [
            ['q' => 'Apakah invoice yang dibuat bisa disimpan?', 'a' => 'Saat ini invoice bisa didownload sebagai PDF. Fitur penyimpanan riwayat invoice tersedia untuk pengguna yang login dengan akun MediaTools.'],
            ['q' => 'Apakah bisa menambahkan logo perusahaan?', 'a' => 'Ya, klik area logo di template invoice dan upload gambar logo perusahaan Anda. Format JPG dan PNG didukung.'],
            ['q' => 'Apakah PPN dan diskon dihitung otomatis?', 'a' => 'Ya, masukkan persentase PPN dan/atau diskon, dan invoice akan menghitung subtotal, potongan, dan total akhir secara otomatis.'],
            ['q' => 'Berapa banyak item yang bisa ditambahkan?', 'a' => 'Tidak ada batasan jumlah item. Tambahkan sebanyak yang Anda butuhkan, sistem akan menyesuaikan layout PDF secara otomatis.'],
            ['q' => 'Apakah format nomor invoice bisa dikustomisasi?', 'a' => 'Ya, format nomor invoice default adalah INV/YYYYMMDD/XXX namun bisa diubah sesuai kebutuhan perusahaan Anda.'],
        ],
        'relatedTools' => [
            ['name' => 'PDF Utilities', 'desc' => 'Gabung & compress PDF', 'url' => '/pdfutilities', 'icon' => 'fa-file-pdf'],
            ['name' => 'QR Code Generator', 'desc' => 'QR untuk QRIS pembayaran', 'url' => '/qr', 'icon' => 'fa-qrcode'],
            ['name' => 'Email Signature', 'desc' => 'Tanda tangan email profesional', 'url' => '/signature', 'icon' => 'fa-signature'],
        ],
    ],

    'mediadownloader' => [
        'title'       => 'Media Downloader — Download YouTube, TikTok, Instagram Gratis',
        'intro'       => 'Media Downloader MediaTools adalah solusi paling mudah untuk mengunduh video dari YouTube, TikTok, Instagram Reels, Facebook, dan 20+ platform lainnya — cukup dengan paste URL dan klik tombol download. Tidak perlu instal software, tidak perlu ekstensi browser.',
        'desc'        => 'Unduh video YouTube hingga kualitas 1080p Full HD, atau konversi langsung ke MP3 untuk menyimpan audio saja. Untuk TikTok, tersedia opsi download tanpa watermark sehingga video bersih tanpa logo merah TikTok. Instagram Reels dan foto carousel juga didukung sepenuhnya. Semua proses terjadi di server kami yang cepat dan aman — file siap diunduh dalam hitungan detik. Gratis tanpa batas, tanpa iklan yang menggangu proses download.',
        'use_cases'   => ['Simpan video tutorial YouTube offline', 'Download lagu dari YouTube ke MP3', 'Download TikTok tanpa watermark untuk re-posting', 'Simpan Instagram Reels favorit', 'Arsip konten untuk keperluan konten kreator', 'Download video untuk presentasi/edukasi'],
        'faq'         => [
            ['q' => 'Bagaimana cara download video YouTube gratis?', 'a' => 'Salin URL video YouTube dari browser, pilih tab YouTube di MediaTools, paste URL di kolom pencarian, pilih format (MP4/MP3) dan kualitas, lalu klik Download. File akan diunduh langsung ke perangkat Anda.'],
            ['q' => 'Apakah bisa download TikTok tanpa watermark?', 'a' => 'Ya. Paste URL video TikTok, pilih opsi "Tanpa Watermark", dan klik download. Hasilnya adalah video MP4 bersih tanpa logo TikTok.'],
            ['q' => 'Apakah bisa convert YouTube ke MP3?', 'a' => 'Bisa. Setelah paste URL YouTube, pilih format "MP3 Audio" dan klik download untuk mendapatkan file audio dalam format MP3.'],
            ['q' => 'Berapa kualitas tertinggi yang bisa didownload?', 'a' => 'Hingga 1080p Full HD untuk YouTube. Kualitas yang tersedia tergantung pada kualitas asli video yang di-upload oleh pemilik konten.'],
            ['q' => 'Apakah download video YouTube legal?', 'a' => 'Mengunduh video untuk keperluan pribadi umumnya diizinkan. Namun menggunakan konten orang lain untuk tujuan komersial tanpa izin melanggar hak cipta. Selalu hormati hak cipta kreator.'],
        ],
        'relatedTools' => [
            ['name' => 'Image Converter', 'desc' => 'Konversi & compress gambar', 'url' => '/imageconverter', 'icon' => 'fa-image'],
            ['name' => 'File Converter', 'desc' => 'Konversi format file', 'url' => '/file-converter', 'icon' => 'fa-arrows-rotate'],
            ['name' => 'PDF Utilities', 'desc' => 'Kelola file PDF', 'url' => '/pdfutilities', 'icon' => 'fa-file-pdf'],
        ],
    ],

    'pdfutilities' => [
        'title'       => 'PDF Utilities — Merge, Split, Compress PDF Online Gratis',
        'intro'       => 'PDF Utilities MediaTools menyediakan semua yang Anda butuhkan untuk mengelola file PDF — gabungkan beberapa PDF menjadi satu, pisahkan halaman tertentu, compress ukuran file, atau rotate halaman — semuanya gratis langsung di browser tanpa perlu menginstal Adobe Acrobat atau software berbayar.',
        'desc'        => 'Compress PDF adalah fitur yang paling banyak digunakan: kurangi ukuran file PDF hingga 90% tanpa kehilangan kualitas yang berarti, sangat berguna untuk mengirim file via email atau WhatsApp yang punya batasan ukuran. Merge PDF memungkinkan Anda menggabungkan proposal, laporan, atau dokumen multi-bagian menjadi satu file PDF yang rapi. Split PDF membantu memisahkan halaman tertentu dari dokumen besar. Semua proses dilakukan di server terenkripsi dan file dihapus otomatis setelah selesai.',
        'use_cases'   => ['Compress proposal bisnis untuk email', 'Gabung laporan keuangan multi-file', 'Pisahkan halaman kontrak tertentu', 'Rotate halaman yang terbalik di scan', 'Compress CV/portofolio untuk lamaran kerja', 'Gabung slip gaji bulanan jadi satu file'],
        'faq'         => [
            ['q' => 'Bagaimana cara compress PDF agar ukurannya lebih kecil?', 'a' => 'Upload file PDF ke PDF Utilities MediaTools, pilih fitur "Compress", dan klik proses. Anda bisa memilih level kompresi sesuai kebutuhan antara kualitas tinggi atau ukuran terkecil.'],
            ['q' => 'Berapa besar file PDF yang bisa diproses?', 'a' => 'Batas maksimal ukuran file adalah 100MB per file untuk operasi merge dan split, dan 50MB untuk compress.'],
            ['q' => 'Apakah merge PDF bisa untuk lebih dari 2 file?', 'a' => 'Ya, Anda bisa menggabungkan hingga 10 file PDF sekaligus. Upload semua file, urutkan sesuai kebutuhan, lalu klik Merge.'],
            ['q' => 'Apakah PDF yang diupload aman?', 'a' => 'Ya. File diproses di server terenkripsi kami dan dihapus otomatis setelah proses selesai atau maksimal dalam 1 jam. Kami tidak menyimpan konten dokumen Anda.'],
            ['q' => 'Apakah PDF yang di-password bisa diproses?', 'a' => 'PDF dengan password tidak bisa langsung diproses. Anda perlu membuka proteksi password terlebih dahulu menggunakan software seperti Adobe Reader sebelum mengupload.'],
        ],
        'relatedTools' => [
            ['name' => 'File Converter', 'desc' => 'PDF ke Word/Excel/JPG', 'url' => '/file-converter', 'icon' => 'fa-arrows-rotate'],
            ['name' => 'Invoice Generator', 'desc' => 'Buat tagihan PDF', 'url' => '/invoice', 'icon' => 'fa-file-invoice'],
            ['name' => 'File Security Scanner', 'desc' => 'Hapus metadata PDF', 'url' => '/sanitizer', 'icon' => 'fa-shield-halved'],
        ],
    ],

    'fileconverter' => [
        'title'       => 'File Converter — Konversi PDF ke Word, Excel, JPG Gratis',
        'intro'       => 'File Converter MediaTools adalah solusi konversi dokumen paling lengkap yang bisa Anda gunakan secara gratis. Konversi PDF ke Word yang bisa diedit, PDF ke Excel dengan tabel yang terjaga, Word ke PDF, dan banyak format lainnya — tanpa perlu berlangganan Adobe, Smallpdf, atau ILovePDF.',
        'desc'        => 'Teknologi konversi kami menggunakan LibreOffice dan Python untuk memastikan hasil konversi semirip mungkin dengan dokumen asli, termasuk formatting, tabel, gambar, dan layout halaman. Konversi PDF ke Word sangat berguna saat Anda menerima kontrak atau laporan PDF yang perlu diedit. PDF ke Excel mempertahankan struktur tabel agar data langsung bisa diolah. Satu sesi bisa memproses hingga 5 file sekaligus — sangat menghemat waktu untuk konversi massal.',
        'use_cases'   => ['Edit kontrak PDF yang diterima dari klien', 'Konversi laporan PDF ke Excel untuk analisis', 'Ubah presentasi Word ke PDF untuk email', 'Konversi gambar JPG ke PDF untuk arsip', 'Batch convert laporan bulanan', 'Ubah nota scan ke format Word'],
        'faq'         => [
            ['q' => 'Bagaimana cara convert PDF ke Word yang bisa diedit?', 'a' => 'Upload file PDF ke File Converter, pilih "PDF ke Word (.docx)", klik Convert, dan download hasilnya. File Word siap diedit dengan Microsoft Word atau Google Docs.'],
            ['q' => 'Apakah hasil konversi mempertahankan formatting asli?', 'a' => 'Konversi kami mempertahankan formatting sebaik mungkin, namun dokumen dengan layout kompleks (multi-kolom, tabel rumit) mungkin perlu sedikit penyesuaian manual setelah konversi.'],
            ['q' => 'Format file apa yang didukung untuk konversi?', 'a' => 'PDF, Word (.docx, .doc), Excel (.xlsx, .xls), PowerPoint (.pptx), JPG, PNG, dan beberapa format dokumen lainnya. Lihat daftar lengkap di halaman tool.'],
            ['q' => 'Berapa file yang bisa dikonversi sekaligus?', 'a' => 'Hingga 5 file dalam satu sesi. Semua file akan diproses secara paralel dan bisa diunduh dalam format ZIP.'],
            ['q' => 'Apakah PDF scan bisa dikonversi ke Word?', 'a' => 'PDF hasil scan (gambar) dikonversi menggunakan OCR (Optical Character Recognition). Hasilnya tergantung kualitas scan — scan yang jelas dan resolusi tinggi akan menghasilkan teks yang lebih akurat.'],
        ],
        'relatedTools' => [
            ['name' => 'PDF Utilities', 'desc' => 'Compress & merge PDF', 'url' => '/pdfutilities', 'icon' => 'fa-file-pdf'],
            ['name' => 'Image Converter', 'desc' => 'Konversi format gambar', 'url' => '/imageconverter', 'icon' => 'fa-image'],
            ['name' => 'File Security Scanner', 'desc' => 'Hapus metadata file', 'url' => '/sanitizer', 'icon' => 'fa-shield-halved'],
        ],
    ],

    'imageconverter' => [
        'title'       => 'Image Converter — Resize, Compress & Konversi Gambar Gratis',
        'intro'       => 'Image Converter MediaTools adalah tools konversi dan kompresi gambar yang berjalan 100% di browser Anda — artinya foto tidak pernah diunggah ke server, privasi Anda sepenuhnya terjaga. Resize dimensi, kompres ukuran file, atau konversi antara JPG, PNG, dan WebP secara instan.',
        'desc'        => 'Kompresi gambar adalah kebutuhan paling umum: foto dari kamera smartphone bisa berukuran 5-15MB, terlalu besar untuk upload ke website, marketplace, atau kirim via WhatsApp. Dengan Image Converter ini, kompres foto menjadi di bawah 500KB atau bahkan 100KB sambil menjaga kualitas visual yang masih layak. Resize dimensi gambar untuk kebutuhan spesifik: foto profil LinkedIn (400×400), thumbnail YouTube (1280×720), atau gambar produk marketplace. Konversi ke WebP untuk website yang lebih cepat karena format WebP 25-35% lebih kecil dari JPEG.',
        'use_cases'   => ['Compress foto produk untuk toko online', 'Resize gambar untuk upload media sosial', 'Konversi JPG ke WebP untuk website lebih cepat', 'Kompres foto untuk kirim via email/WhatsApp', 'Resize thumbnail untuk YouTube/blog', 'Batch compress foto catalog produk'],
        'faq'         => [
            ['q' => 'Apakah foto saya diupload ke server?', 'a' => 'Tidak. Image Converter bekerja sepenuhnya di browser Anda menggunakan JavaScript Canvas API. Foto tidak pernah meninggalkan perangkat Anda, privasi 100% terjaga.'],
            ['q' => 'Berapa ukuran file yang bisa diproses?', 'a' => 'Tidak ada batasan ukuran file karena proses terjadi di browser. Namun untuk performa optimal, disarankan maksimal 20MB per gambar.'],
            ['q' => 'Format apa yang didukung?', 'a' => 'Input: JPG, PNG, WebP, GIF (frame pertama). Output: JPG, PNG, WebP. Konversi antara semua format ini tersedia.'],
            ['q' => 'Berapa banyak gambar yang bisa diproses sekaligus?', 'a' => 'Bisa memproses beberapa gambar sekaligus (batch processing). Jumlah tergantung kapasitas RAM perangkat Anda.'],
            ['q' => 'Apa perbedaan WebP dengan JPG/PNG?', 'a' => 'WebP adalah format modern dari Google yang menghasilkan ukuran file 25-35% lebih kecil dari JPG dengan kualitas visual yang sama. Ideal untuk gambar di website agar halaman lebih cepat.'],
        ],
        'relatedTools' => [
            ['name' => 'Background Remover', 'desc' => 'Hapus background foto AI', 'url' => '/bg', 'icon' => 'fa-wand-magic-sparkles'],
            ['name' => 'File Security Scanner', 'desc' => 'Hapus metadata & GPS foto', 'url' => '/sanitizer', 'icon' => 'fa-shield-halved'],
            ['name' => 'PDF Utilities', 'desc' => 'Gabung gambar ke PDF', 'url' => '/pdfutilities', 'icon' => 'fa-file-pdf'],
        ],
    ],

    'qr' => [
        'title'       => 'QR Code Generator — Buat QR Code Custom & Branded Gratis',
        'intro'       => 'QR Code Generator MediaTools memungkinkan Anda membuat QR Code profesional dan branded dalam hitungan detik — untuk URL website, menu restoran digital, informasi kontak vCard, nomor WhatsApp, QRIS pembayaran, WiFi, atau teks apapun.',
        'desc'        => 'Berbeda dengan generator QR Code biasa yang menghasilkan QR hitam-putih standar, MediaTools memungkinkan Anda mengkustomisasi warna foreground dan background, menambahkan logo di tengah QR Code, dan memilih style titik-titik QR yang unik. Hasilnya bisa didownload sebagai PNG resolusi tinggi atau SVG untuk kebutuhan cetak. QR Code yang Anda buat bersifat permanen dan tidak memerlukan server redirect — cocok untuk menu restoran, kartu nama digital, stiker produk, dan materi marketing bisnis Anda.',
        'use_cases'   => ['QR Code menu digital restoran/kafe', 'QR Code kartu nama untuk networking', 'QR Code WhatsApp business untuk CS', 'QR Code WiFi untuk tamu hotel/co-working', 'QR Code payment di toko fisik', 'QR Code link sosial media di poster'],
        'faq'         => [
            ['q' => 'Apakah QR Code yang dibuat gratis untuk selamanya?', 'a' => 'Ya, QR Code yang Anda buat bersifat statis dan gratis selamanya. Tidak ada biaya berlangganan dan tidak ada tanggal kadaluwarsa.'],
            ['q' => 'Bisakah menambahkan logo di tengah QR Code?', 'a' => 'Bisa. Upload logo perusahaan Anda dan akan otomatis ditempatkan di tengah QR Code. Logo tidak mengganggu kemampuan scan selama ukurannya tidak melebihi 30% area QR.'],
            ['q' => 'Format apa yang tersedia untuk download?', 'a' => 'PNG resolusi tinggi (ideal untuk digital) dan SVG (ideal untuk cetak dalam ukuran berapapun tanpa pecah).'],
            ['q' => 'Apakah QR Code bisa diubah setelah dibuat?', 'a' => 'QR Code statis tidak bisa diubah kontennya setelah dibuat karena data sudah di-encode langsung. Jika konten perlu berubah, buat QR Code baru. Untuk QR Code yang bisa diedit, pertimbangkan QR Code dinamis.'],
            ['q' => 'Berapa banyak karakter yang bisa dimasukkan ke QR Code?', 'a' => 'Hingga 2.953 karakter untuk teks biasa. Namun semakin banyak karakter, semakin padat QR Code dan semakin sulit di-scan. Untuk URL, gunakan URL shortener terlebih dahulu jika linknya panjang.'],
        ],
        'relatedTools' => [
            ['name' => 'LinkTree Builder', 'desc' => 'Satu link untuk semua link', 'url' => '/linktree', 'icon' => 'fa-link'],
            ['name' => 'Invoice Generator', 'desc' => 'Invoice + info pembayaran', 'url' => '/invoice', 'icon' => 'fa-file-invoice'],
            ['name' => 'Email Signature', 'desc' => 'Signature dengan QR Code', 'url' => '/signature', 'icon' => 'fa-signature'],
        ],
    ],

    'passwordgenerator' => [
        'title'       => 'Password Generator — Buat Password Kuat & Aman Instan',
        'intro'       => 'Password Generator MediaTools membantu Anda membuat kata sandi yang kuat, acak, dan aman secara kriptografis — langsung di browser tanpa pernah mengirim data ke server. Semua proses berjalan di perangkat Anda.',
        'desc'        => 'Password yang lemah adalah penyebab utama akun diretas. Password seperti "123456", "password", atau tanggal lahir sangat mudah ditebak atau di-brute force. Password yang kuat terdiri dari kombinasi huruf besar, huruf kecil, angka, dan simbol, minimal 12 karakter, dan unik untuk setiap akun. Generator ini memungkinkan Anda mengatur panjang (8-128 karakter), memilih jenis karakter yang disertakan, dan menghasilkan puluhan password sekaligus untuk langsung dipilih yang paling mudah diingat — atau langsung salin ke password manager Anda.',
        'use_cases'   => ['Password akun email baru', 'Password untuk WiFi router', 'PIN/kode akses aplikasi internal', 'Password database development', 'Secret key API & token', 'Master password untuk password manager'],
        'faq'         => [
            ['q' => 'Apakah password yang dibuat aman?', 'a' => 'Ya. Password dibuat menggunakan Crypto API browser (window.crypto.getRandomValues) yang menghasilkan angka acak yang benar-benar tidak bisa diprediksi. Lebih aman dari Math.random() biasa.'],
            ['q' => 'Apakah password dikirim ke server MediaTools?', 'a' => 'Tidak sama sekali. Semua proses terjadi di browser Anda. Password tidak pernah meninggalkan perangkat Anda.'],
            ['q' => 'Berapa panjang password yang disarankan?', 'a' => 'Minimal 12 karakter untuk keamanan standar. Untuk akun penting seperti email utama, bank, dan password manager, gunakan minimal 20 karakter.'],
            ['q' => 'Apakah harus menggunakan semua jenis karakter?', 'a' => 'Semakin beragam karakter yang digunakan, semakin kuat password. Namun beberapa sistem tidak mendukung karakter khusus — cukup gunakan huruf dan angka jika ada batasan tersebut.'],
            ['q' => 'Bagaimana cara menyimpan password yang kuat ini?', 'a' => 'Gunakan password manager seperti Bitwarden (gratis, open source), 1Password, atau fitur bawaan browser. Jangan simpan password di catatan biasa atau spreadsheet.'],
        ],
        'relatedTools' => [
            ['name' => 'File Security Scanner', 'desc' => 'Lindungi privasi file Anda', 'url' => '/sanitizer', 'icon' => 'fa-shield-halved'],
            ['name' => 'Email Signature', 'desc' => 'Profesionalkan email Anda', 'url' => '/signature', 'icon' => 'fa-signature'],
            ['name' => 'LinkTree Builder', 'desc' => 'Kelola semua link Anda', 'url' => '/linktree', 'icon' => 'fa-link'],
        ],
    ],

    'signature' => [
        'title'       => 'Email Signature Generator — Tanda Tangan Email Profesional Gratis',
        'intro'       => 'Email Signature Generator MediaTools membantu Anda membuat tanda tangan email yang terlihat profesional untuk Gmail, Outlook, Yahoo Mail, dan semua email client — dalam hitungan menit, tanpa perlu keahlian desain atau coding HTML.',
        'desc'        => 'Tanda tangan email yang profesional meningkatkan kesan pertama dalam komunikasi bisnis dan mempermudah klien menemukan kontak Anda. Dengan editor visual intuitif, Anda bisa menambahkan foto profil, nama lengkap, jabatan, nomor telepon, website, akun media sosial, dan bahkan banner promosi. Hasilnya bisa langsung disalin ke pengaturan tanda tangan Gmail atau Outlook — tinggal paste, langsung aktif. Gratis tanpa perlu mendaftar, tersedia beberapa template modern yang bisa dipilih.',
        'use_cases'   => ['Signature email profesional karyawan', 'Tanda tangan freelancer untuk klien', 'Signature dengan logo perusahaan', 'Signature sales dengan CTA tombol', 'Email signature untuk konsultan', 'Tanda tangan dengan banner promosi'],
        'faq'         => [
            ['q' => 'Bagaimana cara pasang tanda tangan di Gmail?', 'a' => 'Buat signature di MediaTools, klik "Salin HTML", buka Gmail Settings → Signature, paste ke kolom tanda tangan. Gmail otomatis menampilkan versi visual.'],
            ['q' => 'Apakah signature bekerja di Outlook?', 'a' => 'Ya. Klik "Salin HTML", buka Outlook → File → Options → Mail → Signatures → Edit, paste di editor. Untuk Outlook versi baru, gunakan mode HTML.'],
            ['q' => 'Apakah foto profil di signature bisa diupload?', 'a' => 'Ya, upload foto profil JPG/PNG dan akan langsung muncul di preview signature. Foto dioptimasi otomatis untuk ukuran email.'],
            ['q' => 'Apakah tanda tangan yang dibuat disimpan?', 'a' => 'Tanda tangan tersimpan otomatis untuk pengguna yang login. Tanpa login, hasil bisa langsung disalin — tidak ada penyimpanan otomatis.'],
            ['q' => 'Berapa banyak template yang tersedia?', 'a' => 'Tersedia beberapa template modern yang bisa dipilih. Setiap template bisa dikustomisasi warna, font, dan layout sesuai preferensi Anda.'],
        ],
        'relatedTools' => [
            ['name' => 'QR Code Generator', 'desc' => 'QR Code untuk kontak Anda', 'url' => '/qr', 'icon' => 'fa-qrcode'],
            ['name' => 'LinkTree Builder', 'desc' => 'Satu link untuk semua link', 'url' => '/linktree', 'icon' => 'fa-link'],
            ['name' => 'Invoice Generator', 'desc' => 'Tagihan profesional gratis', 'url' => '/invoice', 'icon' => 'fa-file-invoice'],
        ],
    ],

    'linktree' => [
        'title'       => 'LinkTree Builder — Buat Halaman Link in Bio Gratis',
        'intro'       => 'LinkTree Builder MediaTools adalah alternatif Linktree gratis terbaik untuk membuat halaman "link in bio" yang cantik dan profesional — untuk Instagram, TikTok, YouTube, Twitter, dan semua media sosial yang hanya mengizinkan satu link di bio.',
        'desc'        => 'Satukan semua link penting Anda dalam satu halaman yang terlihat profesional: website portofolio, toko online, WhatsApp bisnis, channel YouTube, akun media sosial lainnya, atau link apapun yang relevan. Editor visual intuitif memungkinkan Anda mengkustomisasi warna, background, foto profil, dan urutan link tanpa coding. Halaman Anda mendapat URL unik (mediatools.cloud/linktree/username) yang bisa langsung dipasang di bio Instagram atau TikTok Anda.',
        'use_cases'   => ['Link in bio Instagram untuk content creator', 'Bio link TikTok untuk UMKM', 'Halaman landing semua link YouTuber', 'Profil digital freelancer', 'Landing page untuk kampanye iklan', 'Hub link untuk musisi/seniman'],
        'faq'         => [
            ['q' => 'Apa perbedaan MediaTools LinkTree dengan Linktree asli?', 'a' => 'MediaTools LinkTree Builder menawarkan fitur dasar yang sama dengan Linktree gratis — halaman bio link dengan banyak tombol. Keunggulannya: tidak ada branding MediaTools yang mengganggu di paket gratis, dan Anda punya kendali penuh atas data Anda.'],
            ['q' => 'Apakah perlu akun untuk membuat halaman link in bio?', 'a' => 'Ya, dibutuhkan akun MediaTools gratis untuk menyimpan dan mengelola halaman link in bio Anda. Daftar gratis hanya butuh email dan password.'],
            ['q' => 'Berapa banyak link yang bisa ditambahkan?', 'a' => 'Di paket gratis, Anda bisa menambahkan hingga 10 link. Paket premium menawarkan link tidak terbatas beserta fitur analitik klik.'],
            ['q' => 'Apakah URL halaman bisa dikustomisasi?', 'a' => 'Ya, pilih username unik untuk halaman Anda. URL format-nya: mediatools.cloud/linktree/username-anda.'],
            ['q' => 'Apakah ada analitik untuk melihat berapa kali link diklik?', 'a' => 'Analitik klik tersedia di paket premium. Paket gratis menampilkan halaman tanpa statistik klik.'],
        ],
        'relatedTools' => [
            ['name' => 'QR Code Generator', 'desc' => 'QR untuk link in bio Anda', 'url' => '/qr', 'icon' => 'fa-qrcode'],
            ['name' => 'Email Signature', 'desc' => 'Tambahkan ke signature email', 'url' => '/signature', 'icon' => 'fa-signature'],
            ['name' => 'Background Remover', 'desc' => 'Edit foto profil Anda', 'url' => '/bg', 'icon' => 'fa-wand-magic-sparkles'],
        ],
    ],

    'sanitizer' => [
        'title'       => 'File Security Scanner — Hapus Metadata & Lindungi Privasi File',
        'intro'       => 'File Security Scanner MediaTools membantu Anda mendeteksi dan menghapus metadata tersembunyi dari foto dan dokumen — data seperti lokasi GPS, model kamera, nama perangkat, tanggal, dan informasi pribadi lainnya yang secara diam-diam tersimpan di dalam file Anda.',
        'desc'        => 'Setiap foto yang Anda ambil dengan smartphone menyimpan data EXIF yang mencakup koordinat GPS persis lokasi pemotretan, model HP, waktu, dan bahkan nomor seri perangkat. Saat Anda share foto ini ke media sosial atau kirim via email, data ini ikut terbawa — berpotensi mengungkap lokasi rumah atau kantor Anda. File PDF dan dokumen Word juga menyimpan metadata tersembunyi seperti nama penulis, nama perusahaan, riwayat revisi, dan komentar tersembunyi. Tools ini memindai file Anda dan menghapus semua metadata sensitif sebelum Anda membagikannya.',
        'use_cases'   => ['Hapus lokasi GPS dari foto sebelum posting Instagram', 'Bersihkan metadata dokumen sebelum kirim ke klien', 'Lindungi privasi saat jual foto di marketplace', 'Hapus info perusahaan dari PDF sebelum tender', 'Amankan dokumen legal sebelum sharing', 'Bersihkan metadata batch foto produk'],
        'faq'         => [
            ['q' => 'Apa itu metadata dan mengapa berbahaya?', 'a' => 'Metadata adalah data tersembunyi di dalam file foto/dokumen yang berisi informasi seperti lokasi GPS, waktu, model perangkat, dan nama penulis. Jika tidak dihapus, orang yang menerima file bisa mengetahui lokasi Anda atau informasi pribadi lainnya.'],
            ['q' => 'Format file apa yang bisa dibersihkan?', 'a' => 'Foto: JPG, PNG, WebP, TIFF. Dokumen: PDF, DOCX, XLSX, PPTX. Semua format umum didukung.'],
            ['q' => 'Apakah proses ini mengubah kualitas foto?', 'a' => 'Tidak. Menghapus metadata EXIF tidak mempengaruhi kualitas visual foto sama sekali. Gambar tetap terlihat identik, hanya data tersembunyi yang dihapus.'],
            ['q' => 'Apakah file saya aman setelah diupload?', 'a' => 'Ya. File diproses di server terenkripsi dan dihapus otomatis setelah proses selesai atau maksimal dalam 1 jam. Kami tidak menyimpan konten file Anda.'],
            ['q' => 'Bagaimana cara cek apakah foto saya menyimpan lokasi GPS?', 'a' => 'Upload foto ke File Security Scanner MediaTools dan klik "Scan". Hasil scan akan menampilkan semua metadata yang tersimpan di foto, termasuk koordinat GPS jika ada.'],
        ],
        'relatedTools' => [
            ['name' => 'Image Converter', 'desc' => 'Kompres & konversi gambar', 'url' => '/imageconverter', 'icon' => 'fa-image'],
            ['name' => 'Password Generator', 'desc' => 'Buat password yang aman', 'url' => '/password-generator', 'icon' => 'fa-key'],
            ['name' => 'PDF Utilities', 'desc' => 'Kelola file PDF Anda', 'url' => '/pdfutilities', 'icon' => 'fa-file-pdf'],
        ],
    ],

];

// Ambil data untuk tool yang diminta
$data = $toolData[$tool] ?? null;
@endphp

@if($data)
<section class="tool-seo-section" aria-label="Informasi tentang {{ $data['title'] }}">
<div class="tool-seo-inner">

    {{-- ── Deskripsi Tool ── --}}
    <div class="tool-seo-desc-wrap">
        <div class="tool-seo-desc-text">
            <h2 class="tool-seo-h2">{{ $data['title'] }}</h2>
            <p class="tool-seo-intro">{{ $data['intro'] }}</p>
            <p class="tool-seo-body">{{ $data['desc'] }}</p>

            @if(!empty($data['use_cases']))
            <div class="tool-seo-usecases">
                <h3 class="tool-seo-h3">Cocok untuk:</h3>
                <ul class="tool-seo-uc-list">
                    @foreach($data['use_cases'] as $uc)
                    <li><i class="fa-solid fa-circle-check"></i> {{ $uc }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    {{-- ── FAQ ── --}}
    @if(!empty($data['faq']))
    <div class="tool-seo-faq-wrap">
        <h2 class="tool-seo-h2">Pertanyaan yang Sering Ditanyakan</h2>
        <div class="tool-seo-faq-list" style="color:white">
            @foreach($data['faq'] as $i => $item)
            <div class="faq-item {{ $i === 0 ? 'open' : '' }} tool-faq-item">
                <div class="faq-question">
                    <span>{{ $item['q'] }}</span>
                    <span class="faq-icon"><i class="fa-solid fa-plus" style="font-size:10px;"></i></span>
                </div>
                <div class="faq-answer {{ $i === 0 ? 'open' : '' }}">
                    <div class="faq-answer-inner">{{ $item['a'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Related Tools ── --}}
    @if(!empty($data['relatedTools']))
    <div class="tool-seo-related">
        <h2 class="tool-seo-h2">Tools Lain yang Mungkin Anda Butuhkan</h2>
        <div class="tool-seo-related-grid">
            @foreach($data['relatedTools'] as $rt)
            <a href="{{ $rt['url'] }}" class="tool-seo-related-card">
                <div class="tool-seo-related-icon">
                    <i class="fa-solid {{ $rt['icon'] }}"></i>
                </div>
                <div>
                    <div class="tool-seo-related-name">{{ $rt['name'] }}</div>
                    <div class="tool-seo-related-desc">{{ $rt['desc'] }}</div>
                </div>
                <i class="fa-solid fa-arrow-right tool-seo-related-arrow"></i>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
</section>

<style>
.tool-seo-section {
    background: var(--secondary-bg, #071a1a);
    border-top: 1px solid var(--border, rgba(255,255,255,0.06));
    margin-top: 48px;
    padding: 56px 24px 64px;
}
.tool-seo-inner {
    max-width: 860px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 48px;
}
.tool-seo-h2 {
    font-size: clamp(1.2rem, 2.5vw, 1.5rem);
    font-weight: 700;
    color: var(--text-primary, #f0fdf4);
    margin-bottom: 16px;
    letter-spacing: -0.02em;
    line-height: 1.3;
}
.tool-seo-h3 {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--accent, #a3e635);
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.tool-seo-intro {
    font-size: 1rem;
    line-height: 1.75;
    color: var(--text-primary, #f0fdf4);
    margin-bottom: 16px;
    font-weight: 500;
}
.tool-seo-body {
    font-size: 0.925rem;
    line-height: 1.8;
    color: var(--text-dim, #9ca3af);
    margin-bottom: 0;
}
.tool-seo-usecases {
    margin-top: 24px;
}
.tool-seo-uc-list {
    list-style: none;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 8px 16px;
}
.tool-seo-uc-list li {
    font-size: 0.875rem;
    color: var(--text-dim, #9ca3af);
    display: flex;
    align-items: center;
    gap: 8px;
}
.tool-seo-uc-list li i {
    color: var(--accent, #a3e635);
    font-size: 0.75rem;
    flex-shrink: 0;
}
/* FAQ */
.tool-seo-faq-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
/* Related tools */
.tool-seo-related-grid {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.tool-seo-related-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 18px;
    background: var(--card-bg, #0b2323);
    border: 1px solid var(--border, rgba(255,255,255,0.06));
    border-radius: 14px;
    text-decoration: none;
    color: var(--text-primary, #f0fdf4);
    transition: all 0.25s;
}
.tool-seo-related-card:hover {
    border-color: var(--border-accent, rgba(163,230,53,0.2));
    background: var(--card-hover, #0e2a2a);
    transform: translateX(4px);
}
.tool-seo-related-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(163,230,53,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent, #a3e635);
    flex-shrink: 0;
}
.tool-seo-related-name {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 2px;
}
.tool-seo-related-desc {
    font-size: 0.78rem;
    color: var(--text-dim, #9ca3af);
}
.tool-seo-related-arrow {
    margin-left: auto;
    color: var(--text-dim, #9ca3af);
    font-size: 0.75rem;
    flex-shrink: 0;
}
</style>
@endif
