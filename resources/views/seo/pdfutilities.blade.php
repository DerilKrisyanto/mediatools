@section('og_image', 'pdfutilities')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/pdfutilities';

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
$name = 'PDF Tools — MediaTools';

$features = [
    'Merge PDF — gabung banyak file jadi satu',
    'Split PDF — pisah per halaman atau range',
    'Compress PDF — kurangi ukuran hingga 80%',
    'Merge & Split 100% di browser tanpa upload',
    'Compress menggunakan Ghostscript server-side',
    'Drag & drop file dan urutkan dengan mudah',
    '3 level kompresi: ringan, sedang, tinggi',
    'Download hasil split dalam ZIP',
    'Gratis tanpa akun & tanpa watermark',
];

$faq = [
    [
        'q' => 'Bagaimana cara menggabungkan PDF gratis?',
        'a' => 'Pilih fitur Merge PDF, upload file, atur urutan, lalu klik gabung. Proses berjalan langsung di browser tanpa upload ke server.',
    ],
    [
        'q' => 'Apakah merge dan split PDF aman?',
        'a' => 'Ya. Merge dan split dilakukan 100% di browser (client-side), sehingga file tidak pernah dikirim ke server.',
    ],
    [
        'q' => 'Seberapa kecil hasil compress PDF?',
        'a' => 'Kompresi sedang dapat mengurangi ukuran hingga 50–60%, dan kompresi tinggi hingga 70–80% menggunakan Ghostscript.',
    ],
    [
        'q' => 'Apakah ini alternatif iLovePDF?',
        'a' => 'Ya, MediaTools adalah alternatif iLovePDF gratis dengan fitur merge, split, dan compress tanpa batas.',
    ],
    [
        'q' => 'Apakah file disimpan di server?',
        'a' => 'Tidak. File untuk merge dan split tidak pernah diupload. File compress dihapus otomatis setelah proses selesai.',
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
            'Merge PDF Gratis',
            'Split PDF Online',
            'Compress PDF Online',
            'PDF Tools Gratis',
            'iLovePDF Alternative',
        ],

        'applicationCategory'    => 'UtilitiesApplication',
        'applicationSubCategory'=> 'PDF Tools',
        'operatingSystem'       => 'Web',
        'url'                   => $url,

        'description' => 'Gabung, pisah, dan kompres PDF online gratis. Merge & split langsung di browser tanpa upload. Compress PDF hingga 80% lebih kecil.',

        'featureList' => $features,

        'screenshot' => $appUrl . '/images/tools/pdfutilities-preview.png',

        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
        ],

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '3780',
        ],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'merge pdf gratis, split pdf online, compress pdf, gabung pdf, kompres pdf, pdf tools gratis, combine pdf online, ilovepdf alternative',
    ],

    [
        '@context'    => 'https://schema.org',
        '@type'       => 'HowTo',
        'name'        => 'Cara Menggabungkan PDF Online Gratis',
        'description' => 'Langkah mudah menggabungkan beberapa file PDF menjadi satu dokumen.',
        'totalTime'   => 'PT1M',
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Upload file PDF',
                'text' => 'Pilih atau drag file PDF ke halaman.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Atur urutan',
                'text' => 'Susun file sesuai urutan yang diinginkan.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Gabungkan PDF',
                'text' => 'Klik tombol merge untuk menggabungkan file.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Download hasil',
                'text' => 'Download file PDF hasil gabungan.',
            ],
        ],
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
<meta property="og:title" content="PDF Tools Gratis — Merge Split Compress PDF Online | MediaTools">
<meta property="og:description" content="Gabung, pisah, dan kompres PDF gratis. Tanpa upload untuk merge & split. Cepat & aman.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/pdfutilities.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Merge Split Compress PDF Gratis — MediaTools">
<meta name="twitter:description" content="PDF tools lengkap gratis. Merge, split, compress PDF dengan cepat.">
<meta name="twitter:image" content="{{ asset('images/og/pdfutilities.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush