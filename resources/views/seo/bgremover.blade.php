@section('og_image', 'bgremover')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/bg';

/*
|--------------------------------------------------------------------------
| MASTER DATA (Reusable)
|--------------------------------------------------------------------------
*/
$name = 'Background Remover — MediaTools';

$features = [
    'Hapus background foto otomatis dengan AI BiRefNet',
    'Detail rambut & objek halus (alpha matting)',
    'Edit manual dengan brush interaktif',
    'Batch upload hingga 20 gambar',
    'Download PNG transparan HD',
    'Custom background warna & solid',
    'Before / After slider interaktif',
    'Gratis tanpa daftar akun',
];

$faq = [
    [
        'q' => 'Bagaimana cara hapus background foto secara gratis?',
        'a' => 'Upload foto ke MediaTools Background Remover, AI akan menghapus background otomatis dalam hitungan detik. Gratis tanpa daftar.',
    ],
    [
        'q' => 'Apakah tool ini benar-benar gratis?',
        'a' => 'Ya, 100% gratis tanpa batas penggunaan. Tidak perlu akun atau kartu kredit.',
    ],
    [
        'q' => 'Format gambar apa yang didukung?',
        'a' => 'Mendukung JPG, PNG, dan WebP hingga 20MB. Output PNG transparan atau JPG background warna.',
    ],
    [
        'q' => 'Apakah foto saya aman?',
        'a' => 'Ya, file diproses aman dan otomatis dihapus setelah selesai. Privasi terjaga.',
    ],
];

/*
|--------------------------------------------------------------------------
| BUILD FAQ SCHEMA
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
| FINAL SCHEMA (Single JSON-LD)
|--------------------------------------------------------------------------
*/
$schema = [

    // Software Application
    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',
        'name'     => $name,
        'alternateName' => [
            'Hapus Background Foto',
            'Remove Background Online',
            'Remove BG Gratis',
            'Background Eraser Online',
        ],
        'applicationCategory'    => 'MultimediaApplication',
        'applicationSubCategory'=> 'Image Editing',
        'operatingSystem'       => 'Web',
        'url'                   => $url,
        'description'           => 'Hapus background foto otomatis dengan AI. Unggul pada rambut & detail halus. Download PNG transparan gratis tanpa daftar.',
        'featureList'           => $features,
        'screenshot'            => $appUrl . '/images/tools/bgremover-preview.png',
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
            'availability' => 'https://schema.org/InStock',
        ],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '2340',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],
        'inLanguage' => 'id-ID',
        'keywords'   => 'hapus background foto, remove background, background remover, remove bg, transparent background, hapus latar belakang foto',
    ],

    // FAQ
    [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faqSchema,
    ],
];

@endphp

{{-- JSON-LD (1 script only) --}}
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Open Graph --}}
<meta property="og:title" content="Hapus Background Foto Gratis — Remove Background Online | MediaTools">
<meta property="og:description" content="Hapus background foto otomatis dengan AI. Detail rambut halus, hasil PNG transparan, gratis tanpa daftar.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/bgremover.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Hapus Background Foto Gratis — MediaTools">
<meta name="twitter:description" content="Remove background otomatis dengan AI. Gratis, tanpa daftar, hasil HD.">
<meta name="twitter:image" content="{{ asset('images/og/bgremover.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush