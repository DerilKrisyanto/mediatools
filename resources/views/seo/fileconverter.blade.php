@section('og_image', 'fileconverter')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/file-converter';

$name        = 'File Converter Online Gratis — PDF Word Excel JPG PowerPoint | MediaTools';
$description = 'Konversi PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF dan sebaliknya gratis. Batch upload 5 file sekaligus, proses instan, hasil kualitas tinggi. Alternatif iLovePDF & Smallpdf terbaik.';

$features = [
    'Konversi PDF ke Word (DOCX) akurat & cepat',
    'Konversi Word ke PDF dalam hitungan detik',
    'Konversi Excel ke PDF (XLSX & XLS)',
    'Konversi PowerPoint ke PDF (PPTX & PPT)',
    'Konversi JPG / PNG ke PDF satu atau banyak file',
    'Konversi PDF ke JPG (tiap halaman jadi gambar)',
    'Konversi PDF ke PNG resolusi tinggi',
    'Konversi gambar: JPG ↔ PNG ↔ WebP',
    'Batch upload hingga 5 file sekaligus',
    'Download semua hasil dalam ZIP otomatis',
    'Proses instan tanpa antrian, tanpa daftar akun',
    'File dihapus otomatis setelah 30 menit (privasi aman)',
    '100% gratis, tanpa watermark, tanpa batas harian',
    'Support PDF scan dengan OCR otomatis',
    'Alternatif iLovePDF, Smallpdf, Adobe Acrobat Online',
];

$faq = [
    [
        'q' => 'Bagaimana cara convert PDF ke Word gratis?',
        'a' => 'Buka halaman File Converter MediaTools, pilih "PDF → Word", upload file PDF kamu, lalu klik tombol konversi. Proses hanya memerlukan beberapa detik dan hasilnya langsung bisa didownload dalam format DOCX. Gratis, tanpa login, tanpa watermark.',
    ],
    [
        'q' => 'Apakah bisa convert banyak file sekaligus?',
        'a' => 'Ya! MediaTools mendukung upload dan konversi hingga 5 file sekaligus. Semua file diproses berurutan dan bisa didownload satu per satu atau sekaligus dalam format ZIP.',
    ],
    [
        'q' => 'Apakah MediaTools adalah alternatif iLovePDF?',
        'a' => 'Ya, MediaTools adalah alternatif iLovePDF dan Smallpdf yang lebih ringan, gratis sepenuhnya, tanpa login, mendukung batch file, dan menjaga privasi pengguna dengan penghapusan file otomatis.',
    ],
    [
        'q' => 'Apakah file saya aman saat diupload?',
        'a' => 'Semua file yang diupload diproses secara aman di server terenkripsi dan dihapus otomatis dalam 30 menit. Tidak ada yang menyimpan, membaca, atau membagikan dokumen Anda.',
    ],
    [
        'q' => 'Format apa saja yang didukung File Converter MediaTools?',
        'a' => 'MediaTools mendukung konversi: PDF ↔ Word (DOC/DOCX), PDF ↔ Excel (XLS/XLSX), PDF ↔ PowerPoint (PPT/PPTX), PDF ↔ JPG/PNG, serta konversi gambar JPG ↔ PNG ↔ WebP.',
    ],
    [
        'q' => 'Apakah PDF hasil scan bisa dikonversi ke Word?',
        'a' => 'Ya, MediaTools dilengkapi OCR (Optical Character Recognition) otomatis yang memungkinkan konversi PDF berbasis scan/gambar ke Word. Kualitas terbaik untuk PDF yang memiliki teks yang bisa dipilih.',
    ],
    [
        'q' => 'Apakah ada batasan ukuran file?',
        'a' => 'Setiap file maksimal 50 MB. Anda dapat mengupload hingga 5 file sekaligus dalam satu sesi konversi.',
    ],
    [
        'q' => 'Apakah perlu install software atau daftar akun?',
        'a' => 'Tidak! Semua proses konversi berjalan langsung di browser, tanpa perlu mengunduh software apapun dan tanpa perlu membuat akun.',
    ],
];

/*
|--------------------------------------------------------------------------
| SCHEMA: FAQ
|--------------------------------------------------------------------------
*/
$faqEntities = [];
foreach ($faq as $item) {
    $faqEntities[] = [
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
| SCHEMA: HowTo — PDF to Word
|--------------------------------------------------------------------------
*/
$howToSchema = [
    '@context'  => 'https://schema.org',
    '@type'     => 'HowTo',
    'name'      => 'Cara Convert PDF ke Word Gratis di MediaTools',
    'description' => 'Langkah mudah konversi file PDF ke dokumen Word (DOCX) secara gratis di MediaTools.',
    'totalTime' => 'PT1M',
    'supply'    => [
        ['@type' => 'HowToSupply', 'name' => 'File PDF'],
        ['@type' => 'HowToSupply', 'name' => 'Browser (Chrome, Firefox, Safari, Edge)'],
    ],
    'tool' => [
        ['@type' => 'HowToTool', 'name' => 'MediaTools File Converter'],
    ],
    'step' => [
        [
            '@type'    => 'HowToStep',
            'position' => 1,
            'name'     => 'Pilih Jenis Konversi',
            'text'     => 'Buka halaman File Converter MediaTools dan pilih menu "PDF → Word" dari tab konversi.',
            'image'    => $appUrl . '/images/tools/fc-step1.png',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 2,
            'name'     => 'Upload File PDF',
            'text'     => 'Drag & drop file PDF ke area upload, atau klik tombol "Pilih File" untuk memilih dari perangkat Anda. Bisa upload hingga 5 file sekaligus.',
            'image'    => $appUrl . '/images/tools/fc-step2.png',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 3,
            'name'     => 'Mulai Konversi',
            'text'     => 'Klik tombol "Konversi Word → PDF" dan tunggu beberapa detik hingga proses selesai.',
            'image'    => $appUrl . '/images/tools/fc-step3.png',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 4,
            'name'     => 'Download Hasil',
            'text'     => 'Setelah konversi selesai, klik Download untuk mengunduh file DOCX hasil konversi. File diberi nama otomatis "NamaFile - by MediaTools.docx".',
            'image'    => $appUrl . '/images/tools/fc-step4.png',
        ],
    ],
];

/*
|--------------------------------------------------------------------------
| SCHEMA: SoftwareApplication
|--------------------------------------------------------------------------
*/
$softwareSchema = [
    '@context'               => 'https://schema.org',
    '@type'                  => 'SoftwareApplication',
    'name'                   => 'MediaTools File Converter',
    'alternateName'          => [
        'PDF Converter Online Gratis',
        'PDF to Word Converter',
        'Word to PDF Online',
        'Convert PDF Online Indonesia',
        'Alternatif iLovePDF Gratis',
        'Alternatif Smallpdf Indonesia',
        'Konversi File Online Gratis',
        'PDF ke Word Gratis',
    ],
    'applicationCategory'    => 'UtilitiesApplication',
    'applicationSubCategory' => 'File Conversion',
    'operatingSystem'        => 'Web Browser (Chrome, Firefox, Safari, Edge)',
    'url'                    => $url,
    'description'            => $description,
    'featureList'            => $features,
    'screenshot'             => $appUrl . '/images/tools/fileconverter-preview.png',
    'softwareVersion'        => '2.0',
    'datePublished'          => '2024-01-01',
    'dateModified'           => now()->toDateString(),
    'offers' => [
        '@type'         => 'Offer',
        'price'         => '0',
        'priceCurrency' => 'IDR',
        'availability'  => 'https://schema.org/InStock',
        'description'   => 'Gratis selamanya, tanpa daftar akun, tanpa watermark',
    ],
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.9',
        'ratingCount' => '4200',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => $appUrl,
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'pdf to word, word to pdf, convert pdf online, pdf converter gratis, excel to pdf, jpg to pdf, powerpoint to pdf, ilovepdf alternative, smallpdf alternative, konversi file online, pdf ke word gratis, compress pdf, merge pdf indonesia',
];

/*
|--------------------------------------------------------------------------
| SCHEMA: WebPage (BreadcrumbList)
|--------------------------------------------------------------------------
*/
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Beranda',
            'item'     => $appUrl,
        ],
        [
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => 'File Converter',
            'item'     => $url,
        ],
    ],
];

/*
|--------------------------------------------------------------------------
| SCHEMA: WebPage
|--------------------------------------------------------------------------
*/
$webPageSchema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'WebPage',
    '@id'             => $url . '#webpage',
    'url'             => $url,
    'name'            => $name,
    'description'     => $description,
    'inLanguage'      => 'id-ID',
    'isPartOf'        => ['@id' => $appUrl . '/#website'],
    'about'           => ['@id' => $url . '#software'],
    'datePublished'   => '2024-01-01',
    'dateModified'    => now()->toDateString(),
    'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
    'potentialAction' => [
        '@type'  => 'UseAction',
        'target' => $url,
        'object' => ['@type' => 'DigitalDocument'],
    ],
];

$allSchemas = [$softwareSchema, ['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$faqEntities], $howToSchema, $breadcrumbSchema, $webPageSchema];

@endphp

{{-- ── Structured Data ── --}}
<script type="application/ld+json">
{!! json_encode($allSchemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

{{-- ── Canonical & hreflang ── --}}
<link rel="canonical"  href="{{ $url }}">
<link rel="alternate"  hreflang="id"        href="{{ $url }}">
<link rel="alternate"  hreflang="x-default" href="{{ $url }}">

{{-- ── Open Graph ── --}}
<meta property="og:title"            content="Konversi File Gratis — PDF Word Excel JPG PowerPoint Online | MediaTools">
<meta property="og:description"      content="PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF. Batch 5 file sekaligus. Gratis, instan, tanpa login. Alternatif terbaik iLovePDF &amp; Smallpdf.">
<meta property="og:type"             content="website">
<meta property="og:url"              content="{{ $url }}">
<meta property="og:image"            content="{{ asset('images/og/fileconverter.png') }}">
<meta property="og:image:width"      content="1200">
<meta property="og:image:height"     content="630">
<meta property="og:image:alt"        content="File Converter Online Gratis — MediaTools">
<meta property="og:locale"           content="id_ID">
<meta property="og:site_name"        content="MediaTools">

{{-- ── Twitter Card ── --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:site"        content="@mediatools">
<meta name="twitter:title"       content="Convert PDF Online Gratis — Batch 5 File | MediaTools">
<meta name="twitter:description" content="PDF to Word, Word to PDF, Excel to PDF. Batch 5 file sekaligus. Gratis, tanpa daftar, privasi aman.">
<meta name="twitter:image"       content="{{ asset('images/og/fileconverter.png') }}">
<meta name="twitter:image:alt"   content="MediaTools File Converter">

{{-- ── Preload critical resources ── --}}
<link rel="preload" as="style" href="{{ asset('css/fileconverter.css') }}">
<link rel="preload" as="script" href="{{ asset('js/fileconverter.js') }}">

@endpush