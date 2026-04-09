@section('og_image', 'linktree')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/linktree';

/*
|--------------------------------------------------------------------------
| MASTER DATA (HIGH CONVERSION + SEO INTENT)
|--------------------------------------------------------------------------
*/
$name = 'Link in Bio Gratis — Alternatif Linktree Terbaik Indonesia | MediaTools';

$features = [
    'Buat halaman link in bio profesional dalam 1 menit',
    'Custom username URL (namamu)',
    '3 template premium (Dark, Light, Neon)',
    'Upload foto profil & branding sendiri',
    'QR Code otomatis untuk setiap halaman',
    'Statistik klik & pengunjung realtime',
    'Integrasi Instagram, TikTok, WhatsApp, Shopee, Website',
    'Mobile-first & super cepat di semua device',
    'Gratis tanpa watermark',
];

$faq = [
    [
        'q' => 'Apa itu link in bio?',
        'a' => 'Link in bio adalah halaman berisi semua link penting seperti Instagram, TikTok, WhatsApp, dan website dalam satu URL.',
    ],
    [
        'q' => 'Apakah ini alternatif Linktree gratis?',
        'a' => 'Ya, MediaTools adalah alternatif Linktree terbaik di Indonesia dengan fitur lebih lengkap dan harga lebih terjangkau.',
    ],
    [
        'q' => 'Apakah bisa custom tampilan?',
        'a' => 'Bisa. Anda dapat memilih template, warna, foto profil, dan branding sesuai kebutuhan.',
    ],
    [
        'q' => 'Apakah bisa dipakai untuk jualan?',
        'a' => 'Sangat bisa. Cocok untuk UMKM, affiliate, content creator, dan bisnis online.',
    ],
    [
        'q' => 'Apakah ada statistik klik?',
        'a' => 'Ya, tersedia analytics realtime untuk melihat performa link Anda.',
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
| FINAL SCHEMA (PRO MAX LEVEL)
|--------------------------------------------------------------------------
*/
$schema = [

    // CORE SOFTWARE
    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',

        'name' => $name,

        'alternateName' => [
            'Linktree Gratis',
            'Link in Bio Indonesia',
            'Bio Link Creator',
            'Linktree Alternative',
        ],

        'applicationCategory'    => 'SocialNetworkingApplication',
        'applicationSubCategory'=> 'Link in Bio Builder',
        'operatingSystem'       => 'Web',
        'url'                   => $url,

        'description' => 'Buat halaman link in bio gratis untuk Instagram, TikTok, WhatsApp, dan bisnis online. Alternatif Linktree terbaik di Indonesia.',

        'featureList' => $features,

        'screenshot' => $appUrl . '/images/tools/linktree-preview.png',

        'offers' => [
            '@type' => 'AggregateOffer',
            'priceCurrency' => 'IDR',
            'lowPrice'  => '0',
            'highPrice' => '149000',
            'offerCount'=> '3',
        ],

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '4200',
        ],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'linktree gratis, link in bio instagram, bio link tiktok, linktree indonesia, buat link bio, bio link gratis, linktree alternative',
    ],

    // PRODUCT (BOOST CONVERSION + GOOGLE SHOPPING SIGNAL)
    [
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => 'Link in Bio Builder MediaTools',
        'description' => 'Tool untuk membuat halaman link in bio profesional untuk sosial media dan bisnis online.',
        'brand' => [
            '@type' => 'Brand',
            'name'  => 'MediaTools',
        ],
        'offers' => [
            [
                '@type' => 'Offer',
                'name'  => 'Starter',
                'price' => '0',
                'priceCurrency' => 'IDR',
            ],
            [
                '@type' => 'Offer',
                'name'  => 'Pro',
                'price' => '89000',
                'priceCurrency' => 'IDR',
            ],
            [
                '@type' => 'Offer',
                'name'  => 'Business',
                'price' => '149000',
                'priceCurrency' => 'IDR',
            ],
        ],
    ],

    // WEBPAGE
    [
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        'name'     => $name,
        'url'      => $url,
        'description' => 'Buat link in bio gratis untuk semua sosial media dalam satu halaman.',
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
                'name' => 'Link in Bio',
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

{{-- META SEO (CTR BOOST) --}}
<meta name="title" content="Link in Bio Gratis — Alternatif Linktree Terbaik Indonesia">
<meta name="description" content="Buat link in bio untuk Instagram, TikTok & bisnis online. Gratis tanpa watermark + analytics realtime. Alternatif Linktree terbaik Indonesia.">
<meta name="keywords" content="linktree gratis, link in bio instagram, bio link tiktok, linktree indonesia, bio link gratis">

{{-- Open Graph --}}
<meta property="og:title" content="Link in Bio Gratis — Alternatif Linktree Indonesia">
<meta property="og:description" content="Satu link untuk semua sosial media. Gratis, cepat, dan profesional.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/linktree.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Buat Link in Bio Gratis — MediaTools">
<meta name="twitter:description" content="Alternatif Linktree terbaik untuk creator & bisnis online.">
<meta name="twitter:image" content="{{ asset('images/og/linktree.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush