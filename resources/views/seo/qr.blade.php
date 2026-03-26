@section('og_image', 'qr')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/qr';

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
$name = 'QR Code Generator — MediaTools';

$features = [
    'Generate QR Code untuk URL, WiFi, kontak, email, SMS',
    'Custom warna dan background sesuai brand',
    '3 gaya QR: square, dots, rounded',
    'Upload logo di tengah QR Code',
    'Download PNG resolusi tinggi tanpa watermark',
    'Live preview realtime',
    'Cocok untuk menu restoran, QRIS, kartu nama digital',
    'Gratis tanpa akun',
];

$faq = [
    [
        'q' => 'Bagaimana cara membuat QR Code gratis?',
        'a' => 'Masukkan URL atau konten, kustom desain, lalu download QR Code dalam format PNG. Proses instan tanpa daftar.',
    ],
    [
        'q' => 'Apakah bisa membuat QR Code dengan logo?',
        'a' => 'Ya, Anda bisa upload logo brand untuk ditampilkan di tengah QR Code tanpa mengganggu scan.',
    ],
    [
        'q' => 'Apakah QR Code bisa digunakan untuk bisnis?',
        'a' => 'Bisa. QR Code dapat digunakan untuk menu restoran, pembayaran QRIS, kartu nama digital, dan promosi bisnis.',
    ],
    [
        'q' => 'Apakah QR Code akan expired?',
        'a' => 'Tidak. QR Code statis tidak memiliki masa aktif dan bisa digunakan selamanya selama konten masih tersedia.',
    ],
    [
        'q' => 'Apakah QR Code aman digunakan?',
        'a' => 'Ya, QR Code hanya menyimpan data atau link. Pastikan link yang digunakan aman dan terpercaya.',
    ],
];

/*
|--------------------------------------------------------------------------
| BUILD FAQ
|--------------------------------------------------------------------------
*/
$faqSchema = [];
foreach ($faq as $item) {
    $faqSchema[] = [
        '@type' => 'Question',
        'name'  => $item['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text'  => $item['a'],
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| FINAL SCHEMA
|--------------------------------------------------------------------------
*/
$schema = [

    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',

        'name' => $name,

        'alternateName' => [
            'QR Code Generator Gratis',
            'QR Code Maker Online',
            'Create QR Code with Logo',
            'QR Code Bisnis Indonesia',
        ],

        'applicationCategory'    => 'UtilitiesApplication',
        'applicationSubCategory'=> 'QR Code Generator',
        'operatingSystem'       => 'Web',
        'url'                   => $url,

        'description' => 'Buat QR Code gratis untuk bisnis dan personal. Custom logo, warna, dan style. Cocok untuk menu restoran, pembayaran QRIS, dan promosi.',

        'featureList' => $features,

        'screenshot' => $appUrl . '/images/tools/qr-preview.png',

        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
        ],

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '2670',
        ],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'qr code generator gratis, buat qr code, qr code maker, qr code custom logo, qr code bisnis, qr code menu restoran, generate qr code online',
    ],

    [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faqSchema,
    ],

];

@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Open Graph --}}
<meta property="og:title" content="QR Code Generator Gratis — Custom QR Code Bisnis | MediaTools">
<meta property="og:description" content="Buat QR Code custom gratis dengan logo & warna. Cocok untuk menu restoran, QRIS, dan bisnis.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/qr.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="QR Code Generator Gratis — Custom Logo & Warna">
<meta name="twitter:description" content="Buat QR Code untuk bisnis dan personal. Gratis, cepat, tanpa watermark.">
<meta name="twitter:image" content="{{ asset('images/og/qr.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush