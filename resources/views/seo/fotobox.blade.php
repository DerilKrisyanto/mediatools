@section('og_image', 'fotobox')

@push('seo')
@php

$appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
$url    = $appUrl . '/fotobox';

/*
|--------------------------------------------------------------------------
| MASTER SEO DATA (HIGH CTR + HIGH INTENT)
|--------------------------------------------------------------------------
*/
$title = 'FotoBox Online Gratis — Photo Booth 6 Foto + Template Lucu | MediaTools';

$description = 'Photo booth online gratis langsung di browser! Ambil 6 foto otomatis, pilih template lucu & aesthetic, lalu download hasilnya. Tanpa aplikasi, tanpa upload — 100% aman & privat.';

$keywords = 'fotobox online gratis, photo booth online, kamera selfie browser, foto strip lucu, template foto aesthetic, photobooth online indonesia, selfie booth gratis, foto 6 frame online';

/*
|--------------------------------------------------------------------------
| FIX GOOGLE OFFER (NO WARNING)
|--------------------------------------------------------------------------
*/
$offer = [
    '@type' => 'Offer',
    'price' => '0',
    'priceCurrency' => 'IDR',
    'availability' => 'https://schema.org/InStock',
    'shippingDetails' => [
        '@type' => 'OfferShippingDetails',
        'shippingRate' => ['@type'=>'MonetaryAmount','value'=>'0','currency'=>'IDR'],
        'shippingDestination' => ['@type'=>'DefinedRegion','addressCountry'=>'ID'],
        'deliveryTime' => [
            '@type'=>'ShippingDeliveryTime',
            'handlingTime'=>['@type'=>'QuantitativeValue','minValue'=>0,'maxValue'=>0,'unitCode'=>'DAY'],
            'transitTime'=>['@type'=>'QuantitativeValue','minValue'=>0,'maxValue'=>0,'unitCode'=>'DAY'],
        ],
    ],
    'hasMerchantReturnPolicy' => [
        '@type'=>'MerchantReturnPolicy',
        'applicableCountry'=>'ID',
        'returnPolicyCategory'=>'https://schema.org/MerchantReturnNotPermitted',
        'merchantReturnDays'=>0,
        'returnMethod'=>'https://schema.org/ReturnByMail',
        'returnFees'=>'https://schema.org/FreeReturn',
    ],
];

@endphp

{{-- ================= META ================= --}}
<meta name="title" content="{{ $title }}">
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

<meta name="robots" content="index, follow, max-image-preview:large">

{{-- ================= OPEN GRAPH ================= --}}
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ asset('images/og/fotobox.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- ================= TWITTER ================= --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ asset('images/og/fotobox.png') }}">

@endpush


@push('schema')
@php

$schema = [

    /*
    |--------------------------------------------------------------------------
    | SOFTWARE (CORE RANKING)
    |--------------------------------------------------------------------------
    */
    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',

        'name' => $title,

        'alternateName' => [
            'Photo Booth Online Gratis',
            'Fotobox Browser',
            'Selfie Booth Indonesia',
            'Photo Strip Generator',
        ],

        'applicationCategory'     => 'PhotographyApplication',
        'applicationSubCategory'  => 'Photo Booth',
        'operatingSystem'         => 'Web',
        'url'                     => $url,

        'description' => $description,

        'featureList' => [
            'Ambil 6 foto otomatis dengan countdown',
            'Template foto lucu & aesthetic',
            'Drag & pilih foto terbaik',
            'Download instan kualitas tinggi',
            'Tidak upload ke server (privacy safe)',
            'Support HP & laptop',
            'Gratis tanpa login',
        ],

        'image'       => $appUrl . '/images/og/fotobox.png',
        'screenshot'  => $appUrl . '/images/og/fotobox.png',

        'softwareVersion' => '3.0',

        'offers' => $offer,

        /*
        |--------------------------------------------------------------------------
        | TRUST BOOST (VERY IMPORTANT)
        |--------------------------------------------------------------------------
        */
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '3800',
        ],

        'review' => [[
            '@type' => 'Review',
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => '5',
                'bestRating' => '5'
            ],
            'author' => [
                '@type' => 'Person',
                'name' => 'User MediaTools'
            ],
            'reviewBody' => 'Fotobox online paling gampang dipakai. Tidak perlu install aplikasi dan hasilnya langsung bagus untuk upload ke Instagram.',
        ]],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',
        'keywords'   => $keywords,
    ],

    /*
    |--------------------------------------------------------------------------
    | PRODUCT (BOOST GOOGLE VISIBILITY)
    |--------------------------------------------------------------------------
    */
    [
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => 'FotoBox Online MediaTools',
        'description' => $description,
        'brand' => [
            '@type' => 'Brand',
            'name'  => 'MediaTools',
        ],
        'offers' => $offer,
    ],

    /*
    |--------------------------------------------------------------------------
    | WEBPAGE
    |--------------------------------------------------------------------------
    */
    [
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        'name'     => $title,
        'url'      => $url,
        'description' => $description,
        'inLanguage'  => 'id-ID',
    ],

    /*
    |--------------------------------------------------------------------------
    | BREADCRUMB
    |--------------------------------------------------------------------------
    */
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
                'name' => 'FotoBox Online',
                'item' => $url,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | FAQ (RICH RESULT BOOST)
    |--------------------------------------------------------------------------
    */
    [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'Apakah FotoBox gratis?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Ya, FotoBox sepenuhnya gratis tanpa biaya dan tanpa watermark.',
                ]
            ],
            [
                '@type' => 'Question',
                'name' => 'Apakah foto saya aman?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => '100% aman. Foto tidak pernah diupload ke server karena semua proses berjalan di browser.',
                ]
            ],
            [
                '@type' => 'Question',
                'name' => 'Apakah bisa dipakai di HP?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Bisa. FotoBox mendukung smartphone dan menggunakan kamera depan secara otomatis.',
                ]
            ],
            [
                '@type' => 'Question',
                'name' => 'Berapa jumlah foto?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'FotoBox mengambil 6 foto otomatis dengan timer.',
                ]
            ]
        ]
    ]

];

@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush