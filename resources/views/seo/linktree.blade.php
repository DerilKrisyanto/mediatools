{{--
    resources/views/seo/linktree.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: LinkTree Builder
    Target competitor: linktr.ee
    Top 10 keywords:
      1. linktree gratis                6. link in bio instagram
      2. link in bio                    7. halaman bio link
      3. buat link tree                 8. linktree alternative gratis
      4. bio link page                  9. satu link semua sosmed
      5. link hub sosmed               10. link in bio tiktok
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/linktree/index.blade.php:

    1. @section('title', 'Buat LinkTree Gratis — Link in Bio Page Profesional | MediaTools')
    2. @section('meta_description', 'Buat halaman link in bio profesional untuk Instagram, TikTok & semua sosmed dalam hitungan menit. Satu link untuk semua tautan penting Anda. Alternatif Linktree terbaik gratis di Indonesia.')
    3. @section('meta_keywords', 'linktree gratis, link in bio, buat link tree, bio link page, link hub sosmed, link in bio instagram, halaman bio link, linktree alternative gratis, satu link semua sosmed, link in bio tiktok, linktree indonesia, bio link gratis, link in bio page creator, buat bio link, all links one page')
    4. @include('seo.linktree')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'LinkTree Builder — MediaTools',
    'alternateName'       => ['Link in Bio Gratis', 'Buat Bio Link Page', 'Linktree Alternative Indonesia', 'Halaman Bio Link Profesional'],
    'applicationCategory' => 'SocialNetworkingApplication',
    'applicationSubCategory' => 'Bio Link Page Builder',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/linktree',
    'description'         => 'Buat halaman link in bio profesional untuk Instagram, TikTok, dan semua platform sosial media. Satu halaman untuk semua tautan penting Anda. Alternatif Linktree terbaik di Indonesia.',
    'featureList'         => [
        '3 template halaman (Dark, Terang, Neon)',
        'Upload foto profil kustom',
        'QR Code otomatis untuk setiap halaman',
        'Analitik pengunjung realtime',
        'Integrasi Instagram, TikTok, WhatsApp, Website',
        'Badge Verified untuk paket premium',
        'URL custom unik',
        'Bisa dibagikan via link atau QR Code',
    ],
    'offers' => [
        [
            '@type'         => 'Offer',
            'name'          => 'Starter',
            'price'         => '19900',
            'priceCurrency' => 'IDR',
            'description'   => '1 Bulan Akses — 1 Halaman Linktree, 3 Template, QR Code Otomatis',
        ],
        [
            '@type'         => 'Offer',
            'name'          => 'Best Value',
            'price'         => '89000',
            'priceCurrency' => 'IDR',
            'description'   => '6 Bulan Akses — Badge Verified, Analitik Pengunjung',
        ],
        [
            '@type'         => 'Offer',
            'name'          => 'Business',
            'price'         => '149000',
            'priceCurrency' => 'IDR',
            'description'   => '12 Bulan Akses — Link tak terbatas, Prioritas Support',
        ],
    ],
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.8',
        'ratingCount' => '2890',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'linktree gratis, link in bio, bio link page, link hub, linktree alternative, link in bio instagram tiktok',
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Apa itu link in bio dan kenapa perlu?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Link in bio adalah halaman satu URL yang berisi semua link penting Anda (website, sosmed, WhatsApp, dll). Karena Instagram dan TikTok hanya mengizinkan satu link di bio, halaman ini membantu Anda mengarahkan semua follower ke satu tempat.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah linktree ini gratis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Paket dasar membutuhkan langganan mulai Rp 19.900/bulan. Tersedia paket Best Value 6 bulan dan Business 12 bulan dengan harga lebih hemat.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah bisa custom tampilan halaman?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, tersedia 3 template halaman: Dark, Terang, dan Neon. Anda juga bisa upload foto profil kustom.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="Buat LinkTree Gratis — Link in Bio Page Profesional | MediaTools">
<meta property="og:description" content="Satu halaman untuk semua link Instagram, TikTok & sosmed kamu. Alternatif Linktree terbaik di Indonesia.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/linktree">
<meta property="og:image"       content="{{ asset('images/og/linktree.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Buat Link in Bio Page Gratis — MediaTools LinkTree Builder">
<meta name="twitter:description" content="Satu halaman untuk semua link Instagram, TikTok & sosmed kamu. Linktree alternative terbaik.">
<meta name="twitter:image"       content="{{ asset('images/og/linktree.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/linktree">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/linktree">
@endpush
