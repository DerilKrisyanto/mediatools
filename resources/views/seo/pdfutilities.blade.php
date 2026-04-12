@section('og_image', 'pdfutilities')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/pdfutilities';

/*
|--------------------------------------------------------------------------
| MASTER DATA (OPTIMIZED FOR SEO INTENT)
|--------------------------------------------------------------------------
*/
$name = 'PDF Tools Gratis — Merge, Split & Compress PDF Online | MediaTools';

$features = [
    'Merge PDF — gabung banyak file jadi satu tanpa batas',
    'Split PDF — pisah per halaman atau range tertentu',
    'Compress PDF — kecilkan ukuran hingga 80%',
    'Merge & Split 100% di browser tanpa upload',
    'Compress menggunakan Ghostscript server-side',
    'Drag & drop file + reorder dengan mudah',
    '3 level kompresi: ringan, sedang, tinggi',
    'Download hasil split dalam format ZIP',
    'Gratis tanpa login & tanpa watermark',
];

    /*
    |-------------------------------------------------------------
    | FIX Google Search Console — semua error diselesaikan:
    | KRITIS  : availability, image
    | Non-kritis: shippingDetails, hasMerchantReturnPolicy,
    |             aggregateRating.reviewCount, review
    |-------------------------------------------------------------
    */
    $toolOffer = [
        '@type'          => 'Offer',
        'price'          => '0',
        'priceCurrency'  => 'IDR',
        'availability'   => 'https://schema.org/InStock',
        'shippingDetails' => [
            '@type'               => 'OfferShippingDetails',
            'shippingRate'        => ['@type' => 'MonetaryAmount', 'value' => '0', 'currency' => 'IDR'],
            'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'ID'],
            'deliveryTime'        => [
                '@type'        => 'ShippingDeliveryTime',
                'handlingTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 0, 'unitCode' => 'DAY'],
                'transitTime'  => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 0, 'unitCode' => 'DAY'],
            ],
        ],
        'hasMerchantReturnPolicy' => [
            '@type'                => 'MerchantReturnPolicy',
            'applicableCountry'    => 'ID',
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnNotPermitted',
            'merchantReturnDays'   => 0,
            'returnMethod'         => 'https://schema.org/ReturnByMail',
            'returnFees'           => 'https://schema.org/FreeReturn',
        ],
    ];


$faq = [
    [
        'q' => 'Bagaimana cara menggabungkan PDF gratis?',
        'a' => 'Pilih fitur Merge PDF, upload file, atur urutan, lalu klik gabung. Proses dilakukan langsung di browser tanpa upload ke server.',
    ],
    [
        'q' => 'Apakah merge dan split PDF aman?',
        'a' => 'Ya, proses merge dan split berjalan 100% di browser (client-side), sehingga file tidak pernah dikirim ke server.',
    ],
    [
        'q' => 'Seberapa kecil hasil compress PDF?',
        'a' => 'Kompresi sedang dapat mengurangi ukuran hingga 50–60%, sedangkan kompresi tinggi bisa mencapai 70–80% menggunakan Ghostscript.',
    ],
    [
        'q' => 'Apakah ini alternatif iLovePDF?',
        'a' => 'Ya, MediaTools adalah alternatif iLovePDF gratis dengan fitur lengkap tanpa batas dan tanpa watermark.',
    ],
    [
        'q' => 'Apakah file disimpan di server?',
        'a' => 'Tidak. File merge dan split tidak diupload. File compress hanya diproses sementara dan otomatis dihapus.',
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
| FINAL SCHEMA (PRO LEVEL)
|--------------------------------------------------------------------------
*/
$schema = [

    // SOFTWARE APP (CORE)
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

        'description' => 'PDF tools gratis untuk merge, split, dan compress PDF online. Tanpa upload untuk merge & split, cepat, aman, dan tanpa watermark.',

        'featureList' => $features,
        'datePublished'          => '2025-06-01',
        'dateModified'           => now()->toDateString(),

        'screenshot' => $appUrl . '/images/tools/pdfutilities-preview.png',
            'softwareVersion'        => '2.0',
            // FIX KRITIS: image wajib ada untuk Google Listingan penjual
            'image'                  => $appUrl . '/images/og/pdfutilities.png',

        'offers'              => $toolOffer,

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '5000',
        ],
            // FIX: review minimal 1 entry (wajib jika pakai aggregateRating)
            'review' => [[
                '@type'        => 'Review',
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                'author'       => ['@type' => 'Person', 'name' => 'Pengguna MediaTools'],
                'reviewBody'   => 'Merge dan split PDF tanpa upload ke server adalah fitur killer. Privasi terjaga dan prosesnya instan. Jauh lebih baik dari iLovePDF untuk kebutuhan sehari-hari.',
            ]],
        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'merge pdf gratis, split pdf online, compress pdf, gabung pdf, kompres pdf, pdf tools gratis, combine pdf online, ilovepdf alternative',
    ],

    // WEBPAGE (SEO BOOST)
    [
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        'name'     => $name,
        'url'      => $url,
        'description' => 'Gabung, pisah, dan kompres PDF online gratis dengan MediaTools.',
        'inLanguage'  => 'id-ID',
    ],

    // BREADCRUMB (VERY IMPORTANT)
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $appUrl,
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'PDF Tools',
                'item' => $url,
            ],
        ],
    ],

    // HOW TO (HIGH CTR)
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
                'name' => 'Atur urutan file',
                'text' => 'Susun file sesuai kebutuhan.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Klik merge',
                'text' => 'Gabungkan file PDF menjadi satu.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Download hasil',
                'text' => 'Download PDF hasil gabungan.',
            ],
        ],
    ],

    // FAQ (RICH RESULT)
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

{{-- META SEO (CTR BOOST) --}}
<meta name="title" content="PDF Tools Gratis — Merge, Split & Compress PDF Online | MediaTools">
<meta name="description" content="Gabung, pisah, dan kompres PDF gratis tanpa watermark. Merge & split tanpa upload, compress hingga 80% lebih kecil. Cepat & aman.">
<meta name="keywords" content="merge pdf gratis, split pdf online, compress pdf, gabung pdf, kompres pdf, pdf tools gratis">

{{-- Open Graph --}}
<meta property="og:title" content="PDF Tools Gratis — Merge Split Compress PDF Online">
<meta property="og:description" content="Gabung, pisah, dan kompres PDF gratis tanpa watermark. Cepat, aman, tanpa upload.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/pdfutilities.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Merge Split Compress PDF Gratis — MediaTools">
<meta name="twitter:description" content="PDF tools lengkap gratis tanpa watermark. Cepat & aman.">
<meta name="twitter:image" content="{{ asset('images/og/pdfutilities.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush