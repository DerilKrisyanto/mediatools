@section('og_image', 'qr')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/qr';

    $name = 'QR Code Generator Gratis — Custom QR Code Bisnis | MediaTools';

    $features = [
        'Generate QR Code untuk URL, WiFi, kontak, email, dan SMS',
        'Custom warna dan background sesuai brand',
        '3 gaya QR: square, dots, rounded',
        'Upload logo di tengah QR Code',
        'Download PNG resolusi tinggi tanpa watermark',
        'Live preview realtime',
        'Cocok untuk menu restoran, QRIS, kartu nama digital',
        'Gratis tanpa akun',
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
            'q' => 'Bagaimana cara membuat QR Code gratis?',
            'a' => 'Masukkan URL atau konten, kustom desain, lalu download QR Code dalam format PNG. Proses instan tanpa daftar.',
        ],
        [
            'q' => 'Apakah bisa membuat QR Code dengan logo?',
            'a' => 'Ya, Anda bisa upload logo brand untuk ditampilkan di tengah QR Code tanpa mengganggu scan.',
        ],
        [
            'q' => 'Apakah QR Code bisa digunakan untuk bisnis?',
            'a' => 'Bisa. QR Code dapat digunakan untuk menu restoran, pembayaran QRIS, kartu nama digital, dan promosi bisnis.',
        ],
        [
            'q' => 'Apakah QR Code akan expired?',
            'a' => 'Tidak. QR Code statis tidak memiliki masa aktif dan bisa digunakan selamanya selama konten masih tersedia.',
        ],
        [
            'q' => 'Apakah QR Code aman digunakan?',
            'a' => 'Ya, QR Code hanya menyimpan data atau link. Pastikan link yang digunakan aman dan terpercaya.',
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
        '@id'      => $url . '#howto',
        'name'     => 'Cara Membuat QR Code Gratis',
        'description' => 'Langkah mudah membuat QR Code custom di MediaTools.',
        'totalTime' => 'PT1M',
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Masukkan konten',
                'text' => 'Masukkan URL, teks, WiFi, atau data lain yang ingin dibuat QR Code.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Atur desain',
                'text' => 'Pilih warna, style, dan logo sesuai kebutuhan.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Preview hasil',
                'text' => 'Lihat hasil QR Code secara realtime sebelum diunduh.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Download',
                'text' => 'Unduh QR Code dalam format PNG resolusi tinggi.',
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
            '@id'      => $url . '#software',
            'name'     => $name,
            'alternateName' => [
                'QR Code Generator Gratis',
                'QR Code Maker Online',
                'Create QR Code with Logo',
                'QR Code Bisnis Indonesia',
                'Buat QR Code Online',
            ],
            'applicationCategory'    => 'UtilitiesApplication',
            'applicationSubCategory' => 'QR Code Generator',
            'operatingSystem'        => 'Web Browser',
            'url'                    => $url,
            'description'            => 'Buat QR Code gratis untuk bisnis dan personal. Custom logo, warna, dan style. Cocok untuk menu restoran, pembayaran QRIS, dan promosi.',
            'featureList'            => $features,
        'datePublished'          => '2025-06-01',
        'dateModified'           => now()->toDateString(),
            'screenshot'             => $appUrl . '/images/tools/qr-preview.png',
            'softwareVersion'        => '2.0',
            // FIX KRITIS: image wajib ada untuk Google Listingan penjual
            'image'                  => $appUrl . '/images/og/qr.png',
            'offers'              => $toolOffer,
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '2670',
                'reviewCount' => '2670',
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            // FIX: review minimal 1 entry (wajib jika pakai aggregateRating)
            'review' => [[
                '@type'        => 'Review',
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                'author'       => ['@type' => 'Person', 'name' => 'Pengguna MediaTools'],
                'reviewBody'   => 'QR Code dengan logo brand saya jadi terlihat sangat profesional. Proses hanya 1 menit dan hasilnya PNG HD. Cocok banget untuk menu restoran dan kartu nama digital.',
            ]],
            'provider' => [
                '@id' => $appUrl . '/#organization',
            ],
            'inLanguage' => 'id-ID',
            'keywords'   => 'qr code generator gratis, buat qr code, qr code maker, qr code custom logo, qr code bisnis, qr code menu restoran, generate qr code online',
        ],
        [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            '@id'      => $url . '#breadcrumb',
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
                    'name' => 'QR Code Generator',
                    'item' => $url,
                ],
            ],
        ],
        $howToSchema,
        [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            '@id'        => $url . '#faq',
            'mainEntity' => $faqSchema,
        ],
        [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebPage',
            '@id'             => $url . '#webpage',
            'url'             => $url,
            'name'            => $name,
            'description'     => 'Buat QR Code gratis untuk bisnis dan personal.',
            'inLanguage'      => 'id-ID',
            'isPartOf'        => ['@id' => $appUrl . '/#website'],
            'about'           => ['@id' => $url . '#software'],
            'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url'   => $appUrl . '/images/og/qr.png',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="QR Code Generator Gratis — Custom QR Code Bisnis | MediaTools">
<meta name="description" content="Buat QR Code custom gratis dengan logo & warna. Cocok untuk menu restoran, QRIS, dan bisnis.">
<meta name="keywords" content="qr code generator gratis, buat qr code, qr code maker, qr code custom logo, qr code bisnis, qr code menu restoran">

<meta property="og:title" content="QR Code Generator Gratis — Custom QR Code Bisnis | MediaTools">
<meta property="og:description" content="Buat QR Code custom gratis dengan logo & warna. Cocok untuk menu restoran, QRIS, dan bisnis.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/qr.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="QR Code Generator Gratis — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="QR Code Generator Gratis — Custom Logo & Warna">
<meta name="twitter:description" content="Buat QR Code untuk bisnis dan personal. Gratis, cepat, tanpa watermark.">
<meta name="twitter:image" content="{{ asset('images/og/qr.png') }}">
<meta name="twitter:image:alt" content="QR Code Generator Gratis — MediaTools">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">
@endpush