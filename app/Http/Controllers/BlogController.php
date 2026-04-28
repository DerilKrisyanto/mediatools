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
