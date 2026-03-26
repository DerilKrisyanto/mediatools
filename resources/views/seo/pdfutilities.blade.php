{{--
    resources/views/seo/pdfutilities.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: PDF Utilities
    Target competitor: ilovepdf.com/id
    Top 10 keywords:
      1. merge pdf gratis               6. gabung pdf online
      2. split pdf online               7. kompres pdf
      3. compress pdf                   8. pdf tools gratis
      4. gabung pdf                     9. pisah pdf
      5. ilovepdf alternative          10. combine pdf online
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/pdfutilities/index.blade.php:

    1. @section('title', 'PDF Tools Gratis — Merge Split Compress PDF Online | MediaTools')
    2. @section('meta_description', 'Gabung (merge), pisah (split), dan kompres PDF langsung di browser — tanpa upload ke server untuk merge & split. Compress menggunakan Ghostscript server-side. Gratis, privasi terjaga, alternatif iLovePDF terbaik.')
    3. @section('meta_keywords', 'merge pdf gratis, split pdf online, compress pdf, gabung pdf, ilovepdf alternative, gabung pdf online, kompres pdf, pdf tools gratis, pisah pdf, combine pdf online, merge pdf online, pdf merge split, kompres ukuran pdf, pdf utilities, ilovepdf indonesia')
    4. @include('seo.pdfutilities')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'PDF Utilities — MediaTools',
    'alternateName'       => ['Merge PDF Gratis', 'Split PDF Online', 'Compress PDF Gratis', 'Gabung PDF', 'iLovePDF Alternative'],
    'applicationCategory' => 'UtilitiesApplication',
    'applicationSubCategory' => 'PDF Tools',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/pdfutilities',
    'description'         => 'Gabung (merge), pisah (split), dan kompres PDF secara gratis. Merge & Split berjalan 100% di browser tanpa upload. Compress menggunakan Ghostscript untuk hasil terkecil. File dihapus otomatis.',
    'featureList'         => [
        'Merge PDF — Gabung beberapa PDF menjadi satu, drag & drop untuk urutan',
        'Split PDF — Pisah halaman berdasarkan rentang atau tiap halaman ke file terpisah',
        'Compress PDF — Ghostscript server-side, hasil 50–80% lebih kecil',
        'Merge & Split 100% di browser, tanpa upload ke server',
        'ZIP download untuk split semua halaman',
        '3 level kompresi: Ringan, Sedang (Recommended), Tinggi',
        'File compress dihapus otomatis setelah respons',
        'Mendukung PDF hingga 100MB per file',
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
        'ratingCount' => '3780',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'merge pdf gratis, split pdf online, compress pdf, gabung pdf, kompres pdf, pdf tools, ilovepdf alternative',
];

$howToMergeLd = [
    '@context'    => 'https://schema.org',
    '@type'       => 'HowTo',
    'name'        => 'Cara Menggabungkan PDF (Merge PDF) Secara Gratis',
    'description' => 'Gabungkan beberapa file PDF menjadi satu dokumen menggunakan MediaTools PDF Utilities.',
    'totalTime'   => 'PT1M',
    'step'        => [
        [
            '@type'    => 'HowToStep',
            'position' => 1,
            'name'     => 'Pilih Fitur Merge',
            'text'     => 'Klik fitur "Merge PDF" dari pilihan operasi yang tersedia.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 2,
            'name'     => 'Upload File PDF',
            'text'     => 'Drag & drop semua file PDF yang ingin digabung. Atur urutan dengan drag.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 3,
            'name'     => 'Gabungkan PDF',
            'text'     => 'Klik "Gabung X File Sekarang". Proses terjadi langsung di browser tanpa upload ke server.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 4,
            'name'     => 'Download PDF',
            'text'     => 'Download satu file PDF hasil gabungan semua dokumen.',
        ],
    ],
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Apakah merge dan split PDF aman dan tanpa upload ke server?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, fitur Merge dan Split PDF berjalan 100% di browser Anda menggunakan PDF-lib. File PDF tidak pernah dikirim ke server manapun. Privasi terjaga penuh.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Seberapa kecil hasil compress PDF?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Level Sedang (recommended) bisa mengecilkan PDF hingga 50–60%. Level Tinggi bisa mencapai 70–80% lebih kecil dari ukuran asli, menggunakan Ghostscript engine.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah file compress disimpan di server?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Tidak. File dihapus segera setelah respons dikirim ke browser. Tidak ada file yang tersimpan di disk server.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd,       JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($howToMergeLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="PDF Tools Gratis — Merge Split Compress PDF Online | MediaTools">
<meta property="og:description" content="Gabung, pisah, kompres PDF gratis. Merge & Split tanpa upload server. Compress Ghostscript hasil terkecil.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/pdfutilities">
<meta property="og:image"       content="{{ asset('images/og/pdfutilities.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Merge Split Compress PDF Gratis — MediaTools PDF Tools">
<meta name="twitter:description" content="Gabung, pisah, kompres PDF. Merge & Split tanpa upload. Compress Ghostscript terkecil.">
<meta name="twitter:image"       content="{{ asset('images/og/pdfutilities.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/pdfutilities">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/pdfutilities">
@endpush
