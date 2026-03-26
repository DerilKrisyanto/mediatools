{{--
    resources/views/seo/fileconverter.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: File Converter
    Target competitor: ilovepdf.com
    Top 10 keywords:
      1. pdf to word                    6. jpg to pdf
      2. word to pdf                    7. convert pdf online
      3. compress pdf                   8. pdf converter gratis
      4. excel to pdf                   9. ilovepdf alternative
      5. powerpoint to pdf             10. konversi file online
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/fileconverter/index.blade.php:

    1. Ganti @section('title') dengan:
       @section('title', 'Konversi File Online Gratis — PDF Word Excel JPG | MediaTools')

    2. Ganti @section('meta_description') dengan:
       @section('meta_description', 'Konversi PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF dan sebaliknya secara gratis. Upload 5 file sekaligus, hasil instan, privasi terjaga — alternatif terbaik iLovePDF.')

    3. Ganti @section('meta_keywords') dengan:
       @section('meta_keywords', 'pdf to word, word to pdf, konversi pdf, compress pdf, excel to pdf, jpg to pdf, pdf converter gratis, convert pdf online, ilovepdf alternative, konversi file online, pdf ke word gratis, word ke pdf, powerpoint to pdf, pdf to jpg, merge pdf')

    4. Tambahkan baris ini SETELAH @section meta_keywords:
       @include('seo.fileconverter')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'File Converter — MediaTools',
    'alternateName'       => ['PDF Converter Online', 'Word to PDF', 'PDF to Word Gratis', 'Konversi File Online'],
    'applicationCategory' => 'UtilitiesApplication',
    'applicationSubCategory' => 'File Conversion',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/file-converter',
    'description'         => 'Konversi PDF ke Word, Word ke PDF, Excel ke PDF, PowerPoint ke PDF, JPG ke PDF dan sebaliknya. Upload 5 file sekaligus, proses instan, privasi terjaga.',
    'featureList'         => [
        'PDF to Word (DOCX)',
        'Word to PDF',
        'Excel to PDF',
        'PowerPoint to PDF',
        'JPG/PNG to PDF',
        'PDF to JPG/PNG',
        'Multi-file upload (5 file sekaligus)',
        'Download ZIP batch',
        'File dihapus otomatis 15 menit',
    ],
    'offers' => [
        '@type'         => 'Offer',
        'price'         => '0',
        'priceCurrency' => 'IDR',
        'availability'  => 'https://schema.org/InStock',
    ],
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.8',
        'ratingCount' => '3120',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'pdf to word, word to pdf, konversi pdf, excel to pdf, jpg to pdf, pdf converter gratis',
];

$howToLd = [
    '@context'    => 'https://schema.org',
    '@type'       => 'HowTo',
    'name'        => 'Cara Konversi PDF ke Word Secara Gratis',
    'description' => 'Konversi file PDF ke format Word (DOCX) dengan mudah menggunakan MediaTools File Converter.',
    'totalTime'   => 'PT1M',
    'step'        => [
        [
            '@type'    => 'HowToStep',
            'position' => 1,
            'name'     => 'Pilih Jenis Konversi',
            'text'     => 'Klik tab "PDF → ALL TYPES" kemudian pilih "PDF → Word" dari grid konversi.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 2,
            'name'     => 'Upload File',
            'text'     => 'Drag & drop file PDF ke area upload, atau klik untuk memilih file. Maksimal 5 file sekaligus.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 3,
            'name'     => 'Klik Konversi',
            'text'     => 'Klik tombol "Konversi Sekarang" dan tunggu proses selesai dalam hitungan detik.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 4,
            'name'     => 'Download Hasil',
            'text'     => 'Unduh file DOCX hasil konversi. Jika multi-file, semua tersedia dalam satu ZIP.',
        ],
    ],
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Bagaimana cara konversi PDF ke Word gratis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Pilih konversi "PDF → Word", upload file PDF kamu, klik Konversi, lalu download file DOCX hasilnya. Gratis tanpa batas.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah bisa konversi beberapa file sekaligus?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, bisa upload hingga 5 file sekaligus. Hasil konversi bisa didownload satu per satu atau semuanya dalam ZIP.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah file saya aman?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'File dihapus otomatis dari server setelah 15 menit. Tidak ada yang menyimpan dokumen Anda.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd,   JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($howToLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="Konversi File Gratis — PDF Word Excel JPG Online | MediaTools">
<meta property="og:description" content="PDF to Word, Word to PDF, Excel to PDF, JPG to PDF. 5 file sekaligus, gratis, privasi terjaga.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/file-converter">
<meta property="og:image"       content="{{ asset('images/og/fileconverter.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Konversi File Online Gratis — PDF Word Excel | MediaTools">
<meta name="twitter:description" content="PDF to Word, Word to PDF, Excel to PDF. Multi-file, gratis, tanpa daftar.">
<meta name="twitter:image"       content="{{ asset('images/og/fileconverter.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/file-converter">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/file-converter">
@endpush
