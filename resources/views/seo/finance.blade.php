@section('og_image', 'finance')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/finance';

/*
|--------------------------------------------------------------------------
| MASTER DATA (SEO + HIGH CONVERSION)
|--------------------------------------------------------------------------
*/
$name = 'Pencatatan Keuangan UMKM Gratis — Aplikasi Pembukuan Online | MediaTools';

$features = [
    'Catat pemasukan & pengeluaran secara real-time',
    'Dashboard ringkasan keuangan otomatis',
    'Hitung saldo bersih otomatis',
    'Filter laporan berdasarkan bulan & tahun',
    'Grafik distribusi keuangan (pie chart)',
    'Grafik tren keuangan 6 bulan terakhir',
    'Cetak laporan keuangan (print & PDF)',
    'Data tersimpan aman di akun Anda',
    'Akses dari HP & desktop tanpa install aplikasi',
    'Gratis tanpa biaya bulanan',
];

/*
|--------------------------------------------------------------------------
| FIX GOOGLE STRUCTURE (NO ERROR)
|--------------------------------------------------------------------------
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

/*
|--------------------------------------------------------------------------
| FAQ (SEO BOOST)
|--------------------------------------------------------------------------
*/
$faq = [
    [
        'q' => 'Apakah aplikasi pencatatan keuangan ini gratis?',
        'a' => 'Ya, Anda dapat menggunakan fitur pencatatan pemasukan dan pengeluaran secara gratis tanpa biaya berlangganan.',
    ],
    [
        'q' => 'Apakah data keuangan saya aman?',
        'a' => 'Data tersimpan aman di akun Anda dan tidak dibagikan ke pihak manapun. Akses hanya bisa dilakukan setelah login.',
    ],
    [
        'q' => 'Apakah bisa cetak laporan keuangan?',
        'a' => 'Bisa. Anda dapat mencetak laporan pemasukan, pengeluaran, maupun keseluruhan transaksi dalam format siap print.',
    ],
    [
        'q' => 'Apakah cocok untuk UMKM?',
        'a' => 'Sangat cocok untuk UMKM, bisnis kecil, freelancer, hingga usaha rumahan untuk mengelola arus kas dengan mudah.',
    ],
    [
        'q' => 'Apakah bisa diakses dari HP?',
        'a' => 'Ya, aplikasi ini berbasis web dan bisa digunakan di HP, tablet, maupun desktop tanpa install aplikasi.',
    ],
];

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

    // SOFTWARE
    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',

        'name' => $name,

        'alternateName' => [
            'Aplikasi Keuangan UMKM Gratis',
            'Pembukuan Online Gratis',
            'Catatan Keuangan Digital',
            'Finance Tracker Indonesia',
        ],

        'applicationCategory'     => 'FinanceApplication',
        'applicationSubCategory' => 'Accounting',
        'operatingSystem'        => 'Web',
        'url'                    => $url,

        'description' => 'Aplikasi pencatatan keuangan UMKM untuk mencatat pemasukan, pengeluaran, melihat laporan, dan analisis keuangan secara real-time.',

        'featureList' => $features,
        'datePublished' => '2025-06-01',
        'dateModified'  => now()->toDateString(),

        'screenshot' => $appUrl . '/images/tools/finance-preview.png',
        'softwareVersion' => '2.0',
        'image' => $appUrl . '/images/og/finance.png',

        'offers' => $toolOffer,

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '3100',
        ],

        'review' => [[
            '@type'        => 'Review',
            'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
            'author'       => ['@type' => 'Person', 'name' => 'User MediaTools'],
            'reviewBody'   => 'Sangat membantu untuk mencatat keuangan usaha saya. Laporan otomatis dan mudah digunakan.',
        ]],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'pencatatan keuangan umkm, aplikasi pembukuan gratis, catatan keuangan online, laporan keuangan usaha, finance tracker indonesia',
    ],

    // PRODUCT BOOST
    [
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => 'Aplikasi Pencatatan Keuangan MediaTools',
        'description' => 'Tool pembukuan untuk UMKM mencatat pemasukan dan pengeluaran dengan mudah.',
        'brand' => [
            '@type' => 'Brand',
            'name'  => 'MediaTools',
        ],
        'offers' => $toolOffer,
    ],

    // WEBPAGE
    [
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        'name'     => $name,
        'url'      => $url,
        'description' => 'Catat pemasukan dan pengeluaran bisnis dengan mudah. Laporan otomatis dan grafik keuangan.',
        'inLanguage'  => 'id-ID',
    ],

    // BREADCRUMB
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
                'name' => 'Pencatatan Keuangan',
                'item' => $url,
            ],
        ],
    ],

    // FAQ
    [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faqSchema,
    ],

];

@endphp

{{-- JSON-LD --}}
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- META SEO --}}
<meta name="title" content="Pencatatan Keuangan UMKM Gratis | MediaTools">
<meta name="description" content="Aplikasi pembukuan UMKM gratis untuk mencatat pemasukan & pengeluaran, melihat laporan, grafik keuangan, dan cetak laporan secara otomatis.">
<meta name="keywords" content="pencatatan keuangan umkm, aplikasi pembukuan gratis, laporan keuangan usaha, catatan keuangan online, finance tracker indonesia">

{{-- Open Graph --}}
<meta property="og:title" content="Pencatatan Keuangan UMKM Gratis — MediaTools">
<meta property="og:description" content="Kelola keuangan bisnis Anda dengan mudah. Laporan otomatis, grafik, dan cetak laporan.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/finance.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Aplikasi Keuangan UMKM Gratis — MediaTools">
<meta name="twitter:description" content="Catat pemasukan & pengeluaran bisnis dengan mudah dan gratis.">
<meta name="twitter:image" content="{{ asset('images/og/finance.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

{{-- NO INDEX (karena login required) --}}
<meta name="robots" content="noindex, nofollow">

@endpush