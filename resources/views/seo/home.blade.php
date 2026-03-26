{{--
    resources/views/seo/home.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: Home / Landing Page
    Tujuan: Menangkap 1+ kata kunci teratas dari SETIAP tools agar
    halaman home muncul di Google untuk semua pencarian tools kita.
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/home/index.blade.php
    (atau resources/views/welcome.blade.php / index utama):

    1. @section('title', 'MediaTools — Tools Digital Gratis: Invoice PDF, Background Remover, QR Code & 10+ Lainnya')
    2. @section('meta_description', 'Platform tools produktivitas digital 100% gratis untuk UMKM, freelancer, dan creator Indonesia. Hapus background foto, konversi PDF Word, buat invoice, QR Code, link in bio, download video YouTube TikTok, dan 10+ tools gratis lainnya.')
    3. @section('meta_keywords', 'tools digital gratis indonesia, hapus background foto, remove background, pdf to word, word to pdf, merge pdf, invoice generator gratis, buat invoice, link in bio, linktree gratis, download youtube, download tiktok tanpa watermark, qr code generator, buat qr code, email signature gratis, password generator, resize gambar, kompres pdf, tools produktivitas online, media tools')
    4. @include('seo.home')
--}}

@push('seo')
@php

$organizationLd = [
    '@context'     => 'https://schema.org',
    '@type'        => 'Organization',
    'name'         => 'MediaTools',
    'url'          => config('app.url'),
    'logo'         => config('app.url') . '/images/icons-mediatools.png',
    'description'  => 'Platform tools produktivitas digital gratis untuk UMKM, freelancer, dan creator Indonesia.',
    'foundingDate' => '2024',
    'areaServed'   => 'Indonesia',
    'inLanguage'   => 'id-ID',
    'contactPoint' => [
        '@type'             => 'ContactPoint',
        'email'             => 'halo@mediatools.id',
        'contactType'       => 'customer support',
        'availableLanguage' => 'Indonesian',
        'areaServed'        => 'ID',
    ],
    'sameAs' => [
        'https://www.instagram.com/mediatools',
        'https://www.tiktok.com/@mediatools',
    ],
    'hasOfferCatalog' => [
        '@type'     => 'OfferCatalog',
        'name'      => 'MediaTools — Katalog Tools Gratis',
        'itemListElement' => [
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'Background Remover Gratis', 'url' => config('app.url') . '/bg']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'File Converter PDF Word Excel', 'url' => config('app.url') . '/file-converter']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'Image Converter Resize Compress', 'url' => config('app.url') . '/imageconverter']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'Invoice Generator Gratis', 'url' => config('app.url') . '/invoice']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'LinkTree Builder Bio Link', 'url' => config('app.url') . '/linktree']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'Media Downloader YouTube TikTok', 'url' => config('app.url') . '/media-downloader']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'Password Generator Kuat', 'url' => config('app.url') . '/password-generator']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'PDF Utilities Merge Split Compress', 'url' => config('app.url') . '/pdfutilities']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'QR Code Generator Custom', 'url' => config('app.url') . '/qr']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'SoftwareApplication', 'name' => 'Email Signature Generator', 'url' => config('app.url') . '/signature']],
        ],
    ],
];

$websiteLd = [
    '@context'        => 'https://schema.org',
    '@type'           => 'WebSite',
    'name'            => 'MediaTools',
    'url'             => config('app.url'),
    'description'     => 'Platform tools produktivitas digital 100% gratis — Background Remover, PDF Tools, Invoice Generator, QR Code, LinkTree, YouTube Downloader & lebih banyak lagi.',
    'inLanguage'      => 'id-ID',
    'potentialAction' => [
        '@type'       => 'SearchAction',
        'target'      => [
            '@type'       => 'EntryPoint',
            'urlTemplate' => config('app.url') . '/?q={search_term_string}',
        ],
        'query-input' => 'required name=search_term_string',
    ],
];

// BreadcrumbList untuk halaman home
$breadcrumbLd = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'MediaTools',
            'item'     => config('app.url'),
        ],
    ],
];

// ItemList dari semua tools untuk Google rich results
$toolsItemListLd = [
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'name'            => '10+ Tools Digital Gratis — MediaTools',
    'description'     => 'Koleksi tools produktivitas digital gratis untuk UMKM, freelancer, dan creator Indonesia.',
    'numberOfItems'   => 10,
    'itemListElement' => [
        [
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Background Remover Gratis — Hapus Background Foto Online',
            'url'      => config('app.url') . '/bg',
            'description' => 'Hapus background foto otomatis dengan AI BiRefNet. Unggul rambut & detail halus.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => 'PDF to Word & File Converter — Konversi PDF Word Excel',
            'url'      => config('app.url') . '/file-converter',
            'description' => 'Konversi PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF. 5 file sekaligus.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 3,
            'name'     => 'Image Converter — Resize Kompres Konversi Gambar Gratis',
            'url'      => config('app.url') . '/imageconverter',
            'description' => 'Resize, kompres, dan konversi JPG PNG WebP. Tanpa upload ke server.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 4,
            'name'     => 'Invoice Generator — Buat Invoice PDF Profesional Gratis',
            'url'      => config('app.url') . '/invoice',
            'description' => 'Buat invoice tagihan profesional dalam 2 menit. Template siap pakai, PPN otomatis.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 5,
            'name'     => 'LinkTree Builder — Link in Bio Page Gratis',
            'url'      => config('app.url') . '/linktree',
            'description' => 'Buat halaman bio link profesional untuk Instagram, TikTok & semua sosmed.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 6,
            'name'     => 'Media Downloader — Download YouTube TikTok Instagram Gratis',
            'url'      => config('app.url') . '/media-downloader',
            'description' => 'Download video YouTube 1080p, TikTok tanpa watermark, Instagram Reels gratis.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 7,
            'name'     => 'Password Generator — Buat Kata Sandi Kuat & Aman',
            'url'      => config('app.url') . '/password-generator',
            'description' => 'Buat password kuat secara instan. Zero server, privasi 100%, gratis unlimited.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 8,
            'name'     => 'PDF Utilities — Merge Split Compress PDF Online',
            'url'      => config('app.url') . '/pdfutilities',
            'description' => 'Gabung, pisah, kompres PDF gratis. Merge & Split tanpa upload server.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 9,
            'name'     => 'QR Code Generator — Buat QR Code Custom Bisnis',
            'url'      => config('app.url') . '/qr',
            'description' => 'Buat QR Code custom gratis. Custom warna, logo, gaya. Download PNG resolusi tinggi.',
        ],
        [
            '@type'    => 'ListItem',
            'position' => 10,
            'name'     => 'Email Signature Generator — Tanda Tangan Email Profesional',
            'url'      => config('app.url') . '/signature',
            'description' => 'Buat tanda tangan email profesional. Gmail & Outlook ready, gratis unlimited.',
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($organizationLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($websiteLd,        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbLd,     JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($toolsItemListLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="MediaTools — Tools Digital Gratis: Invoice, PDF, Background Remover, QR Code & 10+ Lainnya">
<meta property="og:description" content="Platform tools produktivitas digital 100% gratis. Hapus background foto, konversi PDF, buat invoice, QR Code, link in bio & lebih banyak lagi.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}">
<meta property="og:image"       content="{{ asset('images/og/home.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="MediaTools — 10+ Tools Digital Gratis untuk Indonesia">
<meta name="twitter:description" content="Hapus background foto, konversi PDF, buat invoice, QR Code, link in bio & lebih banyak lagi. Gratis!">
<meta name="twitter:image"       content="{{ asset('images/og/home.png') }}">

<link rel="canonical" href="{{ config('app.url') }}">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}">
<link rel="alternate" hreflang="x-default" href="{{ config('app.url') }}">
@endpush
