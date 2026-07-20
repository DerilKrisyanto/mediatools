<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    /**
     * Data artikel blog — file-based, tidak butuh database tambahan.
     * Tambah artikel baru cukup tambah array di sini.
     */
    private function getAllArticles(): array
    {
        return [
            [
                'slug'        => 'cara-hapus-background-foto-online-gratis',
                'title'       => 'Cara Hapus Background Foto Online Gratis — Tanpa Photoshop, Hasilnya Profesional',
                'description' => 'Panduan lengkap menghapus background foto secara gratis menggunakan AI. Cocok untuk foto produk, pas foto, dan konten media sosial.',
                'keywords'    => 'hapus background foto, remove background gratis, hapus background online, background eraser, remove bg gratis',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-04-10',
                'og_image'    => '/images/og/bgremover.png',
                'read_time'   => '5 menit',
                'tool_url'    => '/bg',
                'tool_name'   => 'Background Remover',
                'content_key' => 'bg_tutorial',
            ],
            [
                'slug'        => 'cara-buat-invoice-freelancer-profesional',
                'title'       => 'Cara Buat Invoice Freelancer yang Profesional — Template Gratis + Tips Agar Cepat Dibayar',
                'description' => 'Panduan membuat invoice freelancer yang profesional dan meyakinkan klien. Termasuk tips elemen wajib invoice dan cara agar tagihan dibayar tepat waktu.',
                'keywords'    => 'cara buat invoice freelancer, template invoice gratis, invoice profesional, tagihan freelancer, invoice pdf gratis',
                'category'    => 'Panduan',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-04-15',
                'og_image'    => '/images/og/invoice.png',
                'read_time'   => '7 menit',
                'tool_url'    => '/invoice',
                'tool_name'   => 'Invoice Generator',
                'content_key' => 'invoice_tutorial',
            ],
            [
                'slug'        => 'cara-download-video-tiktok-tanpa-watermark',
                'title'       => 'Cara Download Video TikTok Tanpa Watermark — Gratis, Cepat, Tanpa Aplikasi',
                'description' => 'Panduan lengkap download video TikTok tanpa watermark secara gratis. Bisa digunakan di HP maupun laptop tanpa install aplikasi.',
                'keywords'    => 'cara download tiktok tanpa watermark, download video tiktok gratis, simpan video tiktok, tiktok downloader gratis',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-04-20',
                'og_image'    => '/images/og/mediadownloader.png',
                'read_time'   => '4 menit',
                'tool_url'    => '/media-downloader',
                'tool_name'   => 'Media Downloader',
                'content_key' => 'tiktok_tutorial',
            ],
            [
                'slug'        => 'cara-compress-pdf-agar-lebih-kecil',
                'title'       => 'Cara Kompres PDF Agar Lebih Kecil Tanpa Mengurangi Kualitas — Gratis Online',
                'description' => 'Kompres file PDF besar menjadi lebih kecil tanpa perlu software berbayar. Cocok untuk kirim via email, WhatsApp, dan upload ke portal pemerintah.',
                'keywords'    => 'cara compress pdf, kompres pdf gratis, pdf lebih kecil, reduce pdf size, compress pdf online',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-04-22',
                'og_image'    => '/images/og/pdfutilities.png',
                'read_time'   => '4 menit',
                'tool_url'    => '/pdfutilities',
                'tool_name'   => 'PDF Utilities',
                'content_key' => 'pdf_tutorial',
            ],
            [
                'slug'        => 'cara-convert-pdf-ke-word-yang-bisa-diedit',
                'title'       => 'Cara Convert PDF ke Word yang Bisa Diedit — Gratis, Hasilnya Rapi',
                'description' => 'Panduan mudah mengubah file PDF menjadi Word (.docx) yang bisa diedit dengan mempertahankan formatting asli. Gratis tanpa daftar.',
                'keywords'    => 'cara convert pdf ke word, pdf ke word gratis, ubah pdf jadi word, pdf to word online, edit file pdf',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-04-25',
                'og_image'    => '/images/og/fileconverter.png',
                'read_time'   => '5 menit',
                'tool_url'    => '/file-converter',
                'tool_name'   => 'File Converter',
                'content_key' => 'pdf_word_tutorial',
            ],
            [
                'slug'        => 'download-video-youtube-tiktok-instagram-sekaligus-hd',
                'title'       => 'Cara Download Video YouTube, TikTok & Instagram Sekaligus dalam Kualitas HD — Gratis Tanpa Watermark',
                'description' => 'Panduan download video dari YouTube, TikTok, dan Instagram Reels sekaligus dalam satu tools yang sama — kualitas HD, tanpa watermark, dan gratis.',
                'keywords'    => 'download tiktok ig hd, youtube mp3 download video tiktok, download video youtube tiktok instagram, mp4 ig tiktok, download video tanpa watermark hd',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-07-14',
                'og_image'    => '/images/og/mediadownloader.png',
                'read_time'   => '5 menit',
                'tool_url'    => '/media-downloader',
                'tool_name'   => 'Media Downloader',
                'content_key' => 'multi_platform_hd_tutorial',
            ],
            [
                'slug'        => 'cara-membuat-link-in-bio-gratis-tanpa-aplikasi',
                'title'       => 'Cara Membuat Link in Bio Gratis — Satukan Semua Link Sosial Media dalam 1 Halaman',
                'description' => 'Panduan membuat halaman link in bio profesional untuk Instagram, TikTok, dan media sosial lainnya. Gratis, tanpa coding, siap pakai dalam 5 menit.',
                'keywords'    => 'linktree maker gratis, cara membuat link in bio, link in bio maker, linktree indonesia, satu link semua sosial media',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-07-21',
                'og_image'    => '/images/og/linktree.png',
                'read_time'   => '5 menit',
                'tool_url'    => '/linktree',
                'tool_name'   => 'Link Tree Builder',
                'content_key' => 'linktree_tutorial',
            ],
            [
                'slug'        => 'cara-membuat-qr-code-custom-untuk-bisnis',
                'title'       => 'Cara Membuat QR Code Custom untuk Menu, Pembayaran & Kontak Bisnis — Gratis',
                'description' => 'Panduan membuat QR Code branded untuk kebutuhan bisnis: menu restoran, pembayaran, kartu nama digital. Desain modern, download HD tanpa watermark.',
                'keywords'    => 'cara membuat qr code, qr code custom gratis, bikin barcode gratis, qr creator online, generator qr code bisnis',
                'category'    => 'Panduan',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-07-28',
                'og_image'    => '/images/og/qr.png',
                'read_time'   => '4 menit',
                'tool_url'    => '/qr',
                'tool_name'   => 'QR Business Kit',
                'content_key' => 'qr_business_tutorial',
            ],
            [
                'slug'        => 'cara-membuat-email-signature-profesional-gratis',
                'title'       => 'Cara Membuat Email Signature Profesional — Bikin Kesan Pertama yang Meyakinkan',
                'description' => 'Panduan mendesain tanda tangan email yang rapi dan profesional untuk Gmail, Outlook, dan email client lainnya. Gratis, tanpa install software.',
                'keywords'    => 'cara membuat email signature, email signature maker gratis, tanda tangan email profesional, template signature email, signature generator online',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-08-04',
                'og_image'    => '/images/og/signature.png',
                'read_time'   => '4 menit',
                'tool_url'    => '/signature',
                'tool_name'   => 'Email Signature',
                'content_key' => 'signature_tutorial',
            ],
            [
                'slug'        => 'cara-membuat-password-kuat-dan-aman',
                'title'       => 'Cara Membuat Password yang Kuat dan Aman — Hindari Akun Diretas',
                'description' => 'Tips membuat password yang sulit ditebak plus cara pakai generator password otomatis. Lindungi akun email, media sosial, dan perbankan online Anda.',
                'keywords'    => 'cara membuat password kuat, password maker gratis, tips password aman, buat password online, password generator otomatis',
                'category'    => 'Panduan',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-08-11',
                'og_image'    => '/images/og/passwordgenerator.png',
                'read_time'   => '5 menit',
                'tool_url'    => '/password-generator',
                'tool_name'   => 'Password Generator',
                'content_key' => 'password_tutorial',
            ],
            [
                'slug'        => 'cara-menghapus-metadata-foto-dokumen-sebelum-dibagikan',
                'title'       => 'Cara Menghapus Metadata Foto & Dokumen Sebelum Dibagikan — Lindungi Privasi Anda',
                'description' => 'Panduan membersihkan metadata tersembunyi (lokasi, perangkat, waktu) dari foto dan file sebelum diunggah ke internet. Gratis dan aman dilakukan online.',
                'keywords'    => 'cara hapus metadata foto, hapus exif data online, privacy scanner file gratis, bersihkan metadata dokumen, deteksi backdoor file',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-08-18',
                'og_image'    => '/images/og/home.png',
                'read_time'   => '5 menit',
                'tool_url'    => '/sanitizer',
                'tool_name'   => 'File Security & Privacy Scanner',
                'content_key' => 'sanitizer_tutorial',
            ],
            [
                'slug'        => 'cara-kompres-konversi-gambar-online-gratis',
                'title'       => 'Cara Kompres & Konversi Gambar Online Gratis — JPG, PNG, WebP Tanpa Kehilangan Kualitas',
                'description' => 'Panduan mengecilkan ukuran file gambar dan mengubah formatnya (JPG, PNG, WebP) langsung di browser. Gratis, cepat, tanpa upload ke server pihak ketiga.',
                'keywords'    => 'cara kompres gambar online, convert gambar ke webp, resize foto gratis, konversi jpg ke png, foto converter online',
                'category'    => 'Tutorial',
                'author'      => 'Tim MediaTools',
                'date'        => '2026-08-25',
                'og_image'    => '/images/og/imageconverter.png',
                'read_time'   => '4 menit',
                'tool_url'    => '/imageconverter',
                'tool_name'   => 'Image Converter',
                'content_key' => 'imageconverter_tutorial',
            ],
        ];
    }

    /** Halaman daftar artikel */
    public function index()
    {
        $articles = $this->getAllArticles();
        // Urutkan terbaru dulu
        usort($articles, fn($a, $b) => strcmp($b['date'], $a['date']));

        return view('blog.index', compact('articles'));
    }

    /** Halaman detail artikel */
    public function show(string $slug)
    {
        $articles = $this->getAllArticles();
        $article  = collect($articles)->firstWhere('slug', $slug);

        if (!$article) {
            abort(404);
        }

        // Artikel terkait (maks 3, exclude yang sedang dibuka)
        $related = collect($articles)
            ->filter(fn($a) => $a['slug'] !== $slug)
            ->shuffle()
            ->take(3)
            ->values()
            ->toArray();

        return view('blog.show', compact('article', 'related'));
    }
}