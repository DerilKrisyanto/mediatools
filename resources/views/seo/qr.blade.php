{{--
    resources/views/seo/qr.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: QR Code Generator
    Target competitor: online-qr-generator.com
    Top 10 keywords:
      1. qr code generator gratis      6. buat qr code bisnis
      2. buat qr code                  7. qr code custom logo
      3. qr code maker                 8. qr code menu restoran
      4. qr code free                  9. generate qr code online
      5. qr code online               10. qr code pembayaran
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/qr/index.blade.php:

    1. @section('title', 'QR Code Generator Gratis — Buat QR Code Custom Bisnis Online | MediaTools')
    2. @section('meta_description', 'Buat QR Code custom gratis untuk menu restoran, pembayaran, kontak, WiFi, dan URL bisnis. Download PNG resolusi tinggi, kustom warna & logo, tanpa watermark, tanpa daftar.')
    3. @section('meta_keywords', 'qr code generator gratis, buat qr code, qr code maker, qr code free, qr code online, buat qr code bisnis, qr code custom logo, qr code menu restoran, generate qr code online, qr code pembayaran, qr code url, qr code wifi, buat qr code custom, qr code download png, qr code creator')
    4. @include('seo.qr')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'QR Code Generator — MediaTools',
    'alternateName'       => ['Buat QR Code Gratis', 'QR Code Maker Online', 'QR Code Custom Logo', 'QR Code Bisnis Indonesia'],
    'applicationCategory' => 'UtilitiesApplication',
    'applicationSubCategory' => 'QR Code Generator',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/qr',
    'description'         => 'Buat QR Code custom gratis untuk URL, menu restoran, pembayaran, WiFi, dan kontak bisnis. Kustom warna, gaya modul, dan upload logo brand. Download PNG resolusi tinggi tanpa watermark.',
    'featureList'         => [
        'QR Code untuk URL, teks, kontak, WiFi, email, SMS',
        'Kustom warna utama dan background',
        '3 gaya modul: Square, Dots, Rounded',
        'Upload branding logo ke tengah QR Code',
        'Download PNG resolusi tinggi',
        'Sync ke cloud (untuk akun terdaftar)',
        'QR Code real-time live preview',
        'Tanpa watermark',
        'Cocok untuk menu restoran, pembayaran QRIS, kartu nama digital',
    ],
    'offers' => [
        '@type'         => 'Offer',
        'price'         => '0',
        'priceCurrency' => 'IDR',
        'availability'  => 'https://schema.org/InStock',
    ],
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.9',
        'ratingCount' => '2670',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'qr code generator gratis, buat qr code, qr code maker, qr code bisnis, qr code custom, menu restoran qr code',
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Bagaimana cara membuat QR Code gratis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Masukkan URL atau konten yang ingin di-encode di kolom "QR Links / Content", kustom warna dan gaya sesuai brand, lalu klik "Download PNG" untuk menyimpan QR Code resolusi tinggi.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah bisa menambahkan logo di QR Code?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, di bagian "Branding Logo (Upload)" kamu bisa upload logo PNG/JPG yang akan tampil di tengah QR Code, tanpa mengurangi kemampuan scan.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'QR Code untuk apa saja yang bisa dibuat?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Bisa untuk URL website, menu restoran digital, pembayaran, kontak bisnis (vCard), WiFi, email, SMS, dan teks biasa. Cocok untuk semua kebutuhan bisnis.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah QR Code yang dibuat bisa discan selamanya?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'QR Code yang didownload adalah file gambar statis yang bisa discan selamanya selama konten URL-nya aktif. Tidak ada server perantara atau kedaluwarsa.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="QR Code Generator Gratis — Custom QR Code Bisnis | MediaTools">
<meta property="og:description" content="Buat QR Code custom gratis. Custom warna, logo brand, gaya modul. Cocok untuk menu restoran, pembayaran, URL bisnis.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/qr">
<meta property="og:image"       content="{{ asset('images/og/qr.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="QR Code Generator Gratis — Custom Logo & Warna | MediaTools">
<meta name="twitter:description" content="Buat QR Code gratis untuk bisnis. Custom warna, logo, gaya modul. Download PNG tanpa watermark.">
<meta name="twitter:image"       content="{{ asset('images/og/qr.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/qr">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/qr">
@endpush
