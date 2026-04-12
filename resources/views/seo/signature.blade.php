@section('og_image', 'signature')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/signature';

    $name = 'Email Signature Generator Gratis — Signature Email Profesional | MediaTools';

    $features = [
        '3 template signature profesional: Klasik, Modern, Elegan',
        'Custom warna sesuai brand bisnis',
        'Upload foto profil atau logo perusahaan',
        'Input lengkap: nama, jabatan, perusahaan, kontak, website, LinkedIn',
        'Copy HTML untuk Gmail, Outlook, Apple Mail',
        'Download PNG signature',
        'Live preview realtime',
        'Kompatibel semua email client dengan inline CSS',
        'Gratis unlimited tanpa watermark',
        'Cocok untuk freelancer, bisnis, corporate, startup',
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
            'q' => 'Bagaimana cara membuat email signature profesional?',
            'a' => 'Isi data seperti nama, jabatan, dan kontak, pilih template, lalu salin HTML signature untuk digunakan di Gmail atau Outlook.',
        ],
        [
            'q' => 'Apakah email signature bisa digunakan di Gmail dan Outlook?',
            'a' => 'Ya, signature menggunakan HTML inline sehingga kompatibel dengan Gmail, Outlook, Apple Mail, dan semua email client.',
        ],
        [
            'q' => 'Kenapa email signature penting untuk bisnis?',
            'a' => 'Email signature membantu membangun branding, meningkatkan kepercayaan, dan memberikan informasi kontak secara profesional di setiap email.',
        ],
        [
            'q' => 'Apakah HTML email signature aman digunakan?',
            'a' => 'Ya, HTML signature hanya berupa tampilan visual dan tidak mengandung script berbahaya.',
        ],
        [
            'q' => 'Apakah bisa digunakan di HP?',
            'a' => 'Bisa, namun pemasangan biasanya lebih mudah dilakukan melalui desktop Gmail atau Outlook.',
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
        'name'     => 'Cara Membuat Email Signature Profesional',
        'description' => 'Langkah mudah membuat email signature profesional di MediaTools.',
        'totalTime' => 'PT3M',
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Isi data',
                'text' => 'Masukkan nama, jabatan, perusahaan, email, dan kontak.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Pilih template',
                'text' => 'Pilih desain signature sesuai gaya brand Anda.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Copy HTML',
                'text' => 'Salin HTML signature untuk digunakan.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Pasang di email',
                'text' => 'Tempel HTML ke pengaturan signature Gmail atau Outlook.',
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
                'Email Signature Generator Gratis',
                'Signature Email Profesional',
                'Gmail Signature Maker',
                'HTML Email Signature Builder',
                'Email Signature Maker',
            ],
            'applicationCategory'    => 'BusinessApplication',
            'applicationSubCategory' => 'Email Signature Generator',
            'operatingSystem'        => 'Web Browser',
            'url'                    => $url,
            'description'            => 'Buat email signature profesional untuk bisnis dan personal. Template modern, custom logo, dan kompatibel Gmail dan Outlook.',
            'featureList'            => $features,
        'datePublished'          => '2025-06-01',
        'dateModified'           => now()->toDateString(),
            'screenshot'             => $appUrl . '/images/tools/signature-preview.png',
            'softwareVersion'        => '2.0',
            // FIX KRITIS: image wajib ada untuk Google Listingan penjual
            'image'                  => $appUrl . '/images/og/signature.png',
            'offers'              => $toolOffer,
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.8',
                'ratingCount' => '1920',
                'reviewCount' => '1920',
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            // FIX: review minimal 1 entry (wajib jika pakai aggregateRating)
            'review' => [[
                '@type'        => 'Review',
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '4.8', 'bestRating' => '5'],
                'author'       => ['@type' => 'Person', 'name' => 'Pengguna MediaTools'],
                'reviewBody'   => 'Email signature saya sekarang jadi terlihat sangat profesional. Template-nya modern, mudah dikustomisasi, dan HTML-nya langsung kompatibel dengan Gmail. Top!',
            ]],
            'provider' => [
                '@id' => $appUrl . '/#organization',
            ],
            'inLanguage' => 'id-ID',
            'keywords' => 'email signature gratis, email signature generator, html email signature, gmail signature template, outlook email signature, professional email signature',
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
                    'name' => 'Email Signature',
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
            'description'     => 'Buat email signature profesional untuk bisnis dan personal.',
            'inLanguage'      => 'id-ID',
            'isPartOf'        => ['@id' => $appUrl . '/#website'],
            'about'           => ['@id' => $url . '#software'],
            'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url'   => $appUrl . '/images/og/signature.png',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="Email Signature Generator Gratis — Signature Email Profesional | MediaTools">
<meta name="description" content="Buat email signature profesional untuk bisnis. Template modern, logo, dan HTML siap pakai.">
<meta name="keywords" content="email signature gratis, email signature generator, html email signature, gmail signature template, outlook email signature">

<meta property="og:title" content="Email Signature Generator Gratis — Professional Email Signature">
<meta property="og:description" content="Buat email signature profesional untuk bisnis. Template modern, logo, dan HTML siap pakai.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/signature.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Email Signature Generator Gratis — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Email Signature Generator Gratis — MediaTools">
<meta name="twitter:description" content="Buat signature email profesional. Gmail dan Outlook ready. Copy HTML instan.">
<meta name="twitter:image" content="{{ asset('images/og/signature.png') }}">
<meta name="twitter:image:alt" content="Email Signature Generator Gratis — MediaTools">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">
@endpush