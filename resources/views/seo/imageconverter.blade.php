section('og_image', 'imageconverter')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/imageconverter';

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
$name = 'Image Converter — MediaTools';

$features = [
    'Resize gambar online (custom & preset)',
    'Kompres foto tanpa kehilangan kualitas signifikan',
    'Konversi JPG ↔ PNG ↔ WebP',
    'WebP ke JPG / PNG',
    'Batch proses hingga 10 gambar',
    'Download ZIP otomatis',
    'Lock aspect ratio saat resize',
    'Zero upload — proses 100% di browser',
    'Privasi aman (file tidak pernah dikirim)',
    'Gratis unlimited tanpa login',
];

$faq = [
    [
        'q' => 'Bagaimana cara resize gambar online gratis?',
        'a' => 'Upload gambar, pilih ukuran atau preset (HD, FHD, IG), lalu klik proses. Semua dilakukan langsung di browser tanpa upload ke server.',
    ],
    [
        'q' => 'Apakah ini aman dan tanpa upload?',
        'a' => 'Ya, semua proses resize, compress, dan convert terjadi 100% di browser. File tidak pernah dikirim ke server.',
    ],
    [
        'q' => 'Apakah ini alternatif FreeConvert?',
        'a' => 'Ya, MediaTools adalah alternatif FreeConvert yang lebih cepat karena tanpa upload server, lebih aman, dan gratis tanpa batas.',
    ],
    [
        'q' => 'Format apa saja yang didukung?',
        'a' => 'Mendukung JPG, JPEG, PNG, WebP, GIF, BMP. Bisa konversi ke JPG, PNG, atau WebP.',
    ],
    [
        'q' => 'Berapa banyak gambar bisa diproses?',
        'a' => 'Hingga 10 gambar sekaligus, dengan opsi download satuan atau ZIP.',
    ],
];

/*
|--------------------------------------------------------------------------
| FAQ SCHEMA
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
        'name'     => $name,
        'alternateName' => [
            'Resize Gambar Online',
            'Image Converter Gratis',
            'Kompres Foto Online',
            'JPG to PNG Online',
            'PNG to JPG Converter',
            'JPG to WebP Converter',
            'Image Resize Online',
            'FreeConvert Alternative',
        ],
        'applicationCategory'    => 'MultimediaApplication',
        'applicationSubCategory'=> 'Image Processing',
        'operatingSystem'       => 'Web',
        'url'                   => $url,
        'description'           => 'Resize, kompres, dan konversi gambar JPG PNG WebP langsung di browser tanpa upload ke server. Cepat, aman, dan gratis unlimited.',
        'featureList'           => $features,
        'screenshot'            => $appUrl . '/images/tools/imageconverter-preview.png',
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
            'availability' => 'https://schema.org/InStock',
        ],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '2200',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],
        'inLanguage' => 'id-ID',
        'keywords'   => 'resize gambar online, kompres foto online, image converter gratis, ubah format gambar, jpg to png, png to jpg, jpg to webp, image resize online, compress gambar gratis',
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
<meta property="og:title" content="Resize Kompres & Konversi Gambar Gratis — JPG PNG WebP | MediaTools">
<meta property="og:description" content="Resize, kompres, dan convert gambar langsung di browser. Tanpa upload, privasi aman, gratis unlimited. Alternatif FreeConvert terbaik.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/imageconverter.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Image Converter Gratis — Resize Kompres JPG PNG WebP">
<meta name="twitter:description" content="Resize, kompres, convert gambar tanpa upload server. Cepat, aman, gratis.">
<meta name="twitter:image" content="{{ asset('images/og/imageconverter.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush