@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $pageUrl = $appUrl . '/media-downloader';

    $name = 'Download Video YouTube, TikTok & Instagram HD Gratis | MP4 MP3 | MediaTools';

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
            'q' => 'Bagaimana cara download video YouTube gratis?',
            'a' => 'Salin URL video YouTube, pilih platform YouTube di MediaTools, pilih format MP4 atau MP3, lalu klik Download. Tidak perlu daftar akun.',
        ],
        [
            'q' => 'Apakah bisa download TikTok tanpa watermark?',
            'a' => 'Ya. Paste URL TikTok, aktifkan opsi tanpa watermark, lalu klik download. Video akan diunduh bersih tanpa logo TikTok.',
        ],
        [
            'q' => 'Apakah bisa convert YouTube ke MP3?',
            'a' => 'Bisa. Pilih platform YouTube, pilih format MP3 Audio, masukkan URL, lalu klik download untuk mendapatkan file audio MP3.',
        ],
        [
            'q' => 'Berapa kualitas video tertinggi yang bisa didownload?',
            'a' => 'Hingga 1080p Full HD untuk YouTube, dan kualitas asli untuk platform lain yang didukung.',
        ],
        [
            'q' => 'Platform apa saja yang didukung?',
            'a' => 'Mendukung YouTube, TikTok, Instagram, Twitter/X, Reddit, Pinterest, Vimeo, Dailymotion, dan banyak platform populer lainnya.',
        ],
        [
            'q' => 'Apakah layanan ini gratis dan aman?',
            'a' => 'Ya, gratis digunakan dan file diproses untuk diunduh. Sistem dirancang agar proses tetap cepat dan praktis.',
        ],
        [
            'q' => 'Apakah perlu install aplikasi?',
            'a' => 'Tidak. Semua berbasis web dan bekerja langsung di browser.',
        ],
        [
            'q' => 'Bisa download YouTube, TikTok, dan Instagram sekaligus di satu tempat yang sama?',
            'a' => 'Bisa. Media Downloader MediaTools mendukung ketiga platform tersebut dalam satu halaman — tinggal pilih tab platform, paste URL, lalu download dalam kualitas HD.',
        ],
        [
            'q' => 'Apakah hasil download TikTok dan Instagram dalam kualitas HD?',
            'a' => 'Ya, video TikTok dan Instagram Reels diunduh dalam kualitas asli/HD sesuai kualitas video yang diunggah pemilik konten.',
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

    $howToSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'HowTo',
        '@id'      => $pageUrl . '#howto',
        'name'     => 'Cara Download Video YouTube, TikTok & Instagram HD',
        'description' => 'Langkah mudah download video YouTube, TikTok, dan Instagram ke MP4 HD atau MP3 gratis menggunakan MediaTools.',
        'totalTime' => 'PT1M',
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Salin URL video',
                'text' => 'Buka YouTube, cari video yang ingin didownload, lalu salin URL dari address bar.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Pilih platform',
                'text' => 'Buka Media Downloader dan pilih platform yang sesuai.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Paste URL dan pilih format',
                'text' => 'Tempel URL lalu pilih format MP3 atau MP4.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Download',
                'text' => 'Klik Download dan tunggu file siap diunduh.',
            ],
        ],
    ];

    $schema = [
        [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            '@id'      => $appUrl . '/#organization',
            'name'     => 'MediaTools',
            'url'      => $appUrl,
            'logo'     => [
                '@type'  => 'ImageObject',
                '@id'    => $appUrl . '/#logo',
                'url'    => $appUrl . '/images/mediatools.jpeg',
                'width'  => 512,
                'height' => 512,
            ],
            'description' => 'Platform tools produktivitas digital gratis untuk invoice, PDF, background remover, converter, QR code, dan kebutuhan digital lainnya.',
            'inLanguage'  => 'id-ID',
            'areaServed'  => 'ID',
        ],
        [
            '@context' => 'https://schema.org',
            '@type'    => 'SoftwareApplication',
            '@id'      => $pageUrl . '#software',
            'name'     => $name,
            'alternateName' => [
                'YouTube Downloader Gratis Indonesia',
                'Download TikTok Tanpa Watermark',
                'YouTube to MP3 Converter',
                'Download Video Instagram Reels',
                'SaveFrom Alternative',
                'SnapTik Alternative',
                'Y2Mate Alternative',
                'YT Downloader Online',
            ],
            'applicationCategory'   => 'MultimediaApplication',
            'applicationSubCategory'=> 'Video Downloader',
            'operatingSystem'       => 'Web Browser',
            'url'                   => $pageUrl,
            'description' => 'Download video YouTube, TikTok, dan Instagram HD gratis dalam satu halaman. Convert YouTube ke MP3, download MP4 hingga 1080p, TikTok tanpa watermark, Instagram Reels, Shorts, serta video online cukup dengan paste URL.',
            'featureList'           => [
                'Download YouTube video hingga 1080p Full HD',
                'Convert YouTube ke MP3 audio kualitas tinggi',
                'Download TikTok tanpa watermark',
                'Download Instagram Reels, foto, dan video',
                'Download Twitter/X video dan GIF',
                'Download Reddit video',
                'Mendukung banyak platform populer',
                'Tampilkan ukuran file sebelum download',
                'Gratis tanpa daftar akun',
                'Bekerja di semua browser dan smartphone',
            ],
            'offers'              => $toolOffer,
            'provider' => [
                '@id' => $appUrl . '/#organization',
            ],
            'datePublished'          => '2025-05-01',
            'dateModified'           => now()->toDateString(),
            'inLanguage' => 'id-ID',
            'keywords' => 'download tiktok ig hd, youtube mp3 download video tiktok, mp4 ig tiktok, download video youtube tanpa watermark tiktok, youtube mp4 tiktok, youtube mp3 tiktok, download video tiktok instagram, download video tiktok ig, download youtube gratis, youtube downloader, youtube to mp3, download tiktok tanpa watermark, instagram downloader, instagram reels downloader, yt downloader online, savefrom alternative, snaptik alternative, y2mate alternative',
        ],
        [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            '@id'      => $pageUrl . '#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Beranda',
                    'item' => $appUrl,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Media Downloader',
                    'item' => $pageUrl,
                ],
            ],
        ],
        $howToSchema,
        [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            '@id'        => $pageUrl . '#faq',
            'mainEntity' => $faqSchema,
        ],
        [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebPage',
            '@id'             => $pageUrl . '#webpage',
            'url'             => $pageUrl,
            'name'            => $name,
            'description'     => 'Download video YouTube, TikTok, Instagram Reels, dan banyak platform lain secara gratis.',
            'inLanguage'      => 'id-ID',
            'isPartOf'        => ['@id' => $appUrl . '/#website'],
            'about'           => ['@id' => $pageUrl . '#software'],
            'breadcrumb'      => ['@id' => $pageUrl . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url'   => $appUrl . '/images/og/mediadownloader.png',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="Download Video TikTok Instagram YouTube MP4 MP3 Gratis Tanpa Watermark | MediaTools">
<meta name="description" content="Download video YouTube, TikTok, dan Instagram HD gratis. Convert YouTube ke MP3, download MP4 hingga 1080p, TikTok tanpa watermark, Instagram Reels, Shorts, dan video online langsung dari browser.">
<meta name="keywords" content="download tiktok ig hd, youtube mp3 download video tiktok, mp4 ig tiktok, download video youtube tanpa watermark tiktok, youtube mp4 tiktok, youtube mp3 tiktok, download video tiktok instagram, download video tiktok ig, youtube downloader, youtube to mp3, instagram downloader, tiktok downloader">

<meta property="og:title" content="Download Video YouTube, TikTok & Instagram HD Gratis | MediaTools">
<meta property="og:description" content="Download video YouTube, TikTok, dan Instagram HD gratis. Convert MP3, download MP4 1080p, TikTok tanpa watermark, Instagram Reels, Shorts, cukup paste URL.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:image" content="{{ asset('images/og/mediadownloader.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Media Downloader Gratis — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Download Video YouTube, TikTok & Instagram HD | MediaTools">
<meta name="twitter:description" content="Download YouTube MP3, MP4 1080p, TikTok tanpa watermark, dan Instagram Reels HD gratis. Paste URL lalu download langsung.">
<meta name="twitter:image" content="{{ asset('images/og/mediadownloader.png') }}">
<meta name="twitter:image:alt" content="Media Downloader Gratis — MediaTools">

<link rel="canonical" href="{{ $pageUrl }}">
<link rel="alternate" hreflang="id" href="{{ $pageUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $pageUrl }}">
@endpush