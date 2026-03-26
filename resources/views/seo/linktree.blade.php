@section('og_image', 'linktree')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/linktree';

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
$name = 'Link in Bio Builder — MediaTools';

$features = [
    'Buat halaman link in bio profesional dalam hitungan menit',
    '3 template modern (Dark, Terang, Neon)',
    'Upload foto profil & branding kustom',
    'QR Code otomatis untuk setiap halaman',
    'Analitik klik & pengunjung realtime',
    'Integrasi Instagram, TikTok, WhatsApp, Website',
    'Custom URL unik (username link)',
    'Satu halaman untuk semua link penting',
];

$faq = [
    [
        'q' => 'Apa itu link in bio?',
        'a' => 'Link in bio adalah satu halaman berisi semua link penting Anda seperti Instagram, TikTok, WhatsApp, dan website. Digunakan karena platform sosial hanya mengizinkan satu link di bio.',
    ],
    [
        'q' => 'Apakah ini alternatif Linktree gratis?',
        'a' => 'Ya, MediaTools menyediakan alternatif Linktree dengan fitur lengkap dan harga lebih terjangkau untuk pengguna di Indonesia.',
    ],
    [
        'q' => 'Apakah bisa custom tampilan halaman?',
        'a' => 'Bisa. Anda dapat memilih template, upload foto profil, dan menyesuaikan tampilan sesuai branding.',
    ],
    [
        'q' => 'Apakah bisa digunakan untuk Instagram dan TikTok?',
        'a' => 'Ya, halaman link ini bisa digunakan untuk Instagram, TikTok, Twitter, dan semua platform sosial media.',
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
| FINAL SCHEMA (SINGLE SCRIPT)
|--------------------------------------------------------------------------
*/
$schema = [

    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',
        'name'     => $name,
        'alternateName' => [
            'Linktree Gratis',
            'Link in Bio Page',
            'Bio Link Creator',
            'Linktree Alternative Indonesia',
        ],
        'applicationCategory'    => 'SocialNetworkingApplication',
        'applicationSubCategory'=> 'Bio Link Builder',
        'operatingSystem'       => 'Web',
        'url'                   => $url,
        'description'           => 'Buat halaman link in bio profesional untuk Instagram, TikTok, dan semua sosial media. Satu link untuk semua tautan penting Anda.',
        'featureList'           => $features,
        'screenshot'            => $appUrl . '/images/tools/linktree-preview.png',

        'offers' => [
            [
                '@type' => 'Offer',
                'name'  => 'Starter',
                'price' => '19900',
                'priceCurrency' => 'IDR',
            ],
            [
                '@type' => 'Offer',
                'name'  => 'Best Value',
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

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.8',
            'ratingCount' => '2890',
        ],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'linktree gratis, link in bio, bio link page, buat link tree, link hub sosmed, link in bio instagram, link in bio tiktok, satu link semua sosmed, linktree indonesia, bio link gratis',
    ],

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

{{-- Open Graph --}}
<meta property="og:title" content="Buat LinkTree Gratis — Link in Bio Page Profesional | MediaTools">
<meta property="og:description" content="Satu halaman untuk semua link Instagram, TikTok & sosial media. Alternatif Linktree terbaik di Indonesia.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/linktree.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Link in Bio Gratis — MediaTools">
<meta name="twitter:description" content="Buat bio link page untuk semua sosial media dalam 1 link. Alternatif Linktree terbaik.">
<meta name="twitter:image" content="{{ asset('images/og/linktree.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush