@section('og_image', 'fileconverter')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/file-converter';

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
$name = 'File Converter — MediaTools';

$features = [
    'PDF ke Word (DOCX) akurat',
    'Word ke PDF cepat & rapi',
    'Excel ke PDF (XLSX/XLS)',
    'PowerPoint ke PDF (PPTX/PPT)',
    'JPG/PNG ke PDF',
    'PDF ke JPG/PNG',
    'Batch upload hingga 5 file',
    'Download ZIP otomatis',
    'Proses instan tanpa antrian',
    'File dihapus otomatis (privasi aman)',
    'Gratis tanpa daftar akun',
];

$faq = [
    [
        'q' => 'Bagaimana cara convert PDF ke Word gratis?',
        'a' => 'Upload file PDF, pilih PDF ke Word, klik konversi, lalu download hasil DOCX. Proses hanya beberapa detik dan gratis tanpa login.',
    ],
    [
        'q' => 'Apakah bisa convert banyak file sekaligus?',
        'a' => 'Ya, kamu bisa upload hingga 5 file sekaligus dan download semua hasil dalam ZIP.',
    ],
    [
        'q' => 'Apakah ini alternatif iLovePDF?',
        'a' => 'Ya, MediaTools adalah alternatif iLovePDF yang lebih ringan, gratis, tanpa login, dan mendukung banyak format konversi.',
    ],
    [
        'q' => 'Apakah file saya aman?',
        'a' => 'Semua file otomatis dihapus setelah beberapa menit. Tidak disimpan atau dibagikan.',
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
| HOW TO SCHEMA
|--------------------------------------------------------------------------
*/
$howToSchema = [
    '@type' => 'HowTo',
    'name' => 'Cara Konversi PDF ke Word Gratis',
    'totalTime' => 'PT1M',
    'step' => [
        [
            '@type' => 'HowToStep',
            'position' => 1,
            'name' => 'Pilih konversi',
            'text' => 'Pilih menu PDF ke Word di halaman converter.',
        ],
        [
            '@type' => 'HowToStep',
            'position' => 2,
            'name' => 'Upload file',
            'text' => 'Upload file PDF dari perangkat kamu.',
        ],
        [
            '@type' => 'HowToStep',
            'position' => 3,
            'name' => 'Konversi',
            'text' => 'Klik tombol konversi dan tunggu beberapa detik.',
        ],
        [
            '@type' => 'HowToStep',
            'position' => 4,
            'name' => 'Download',
            'text' => 'Download hasil dalam format Word (DOCX).',
        ],
    ],
];

/*
|--------------------------------------------------------------------------
| FINAL SCHEMA (1 SCRIPT ONLY)
|--------------------------------------------------------------------------
*/
$schema = [

    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',
        'name'     => $name,
        'alternateName' => [
            'PDF Converter Online',
            'PDF to Word Gratis',
            'Word to PDF Online',
            'Convert PDF Online',
            'iLovePDF Alternative',
            'Konversi File Online',
        ],
        'applicationCategory'    => 'UtilitiesApplication',
        'applicationSubCategory'=> 'File Conversion',
        'operatingSystem'       => 'Web',
        'url'                   => $url,
        'description'           => 'Konversi PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF dan lainnya secara gratis. Cepat, aman, tanpa login.',
        'featureList'           => $features,
        'screenshot'            => $appUrl . '/images/tools/fileconverter-preview.png',
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
            'availability' => 'https://schema.org/InStock',
        ],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '3500',
        ],
        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],
        'inLanguage' => 'id-ID',
        'keywords'   => 'pdf to word, word to pdf, convert pdf online, pdf converter gratis, excel to pdf, jpg to pdf, powerpoint to pdf, ilovepdf alternative, konversi file online',
    ],

    [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => $faqSchema,
    ],

    array_merge(['@context' => 'https://schema.org'], $howToSchema),

];

@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Open Graph --}}
<meta property="og:title" content="Konversi File Gratis — PDF Word Excel JPG Online | MediaTools">
<meta property="og:description" content="PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF. Gratis, cepat, tanpa login. Alternatif iLovePDF terbaik.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/fileconverter.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Convert PDF Online Gratis — MediaTools">
<meta name="twitter:description" content="PDF to Word, Word to PDF, Excel to PDF. Cepat, gratis, tanpa daftar.">
<meta name="twitter:image" content="{{ asset('images/og/fileconverter.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush