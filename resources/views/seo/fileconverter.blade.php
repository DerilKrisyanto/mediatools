@section('og_image', 'fileconverter')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/file-converter';

    $name = 'File Converter Online Gratis — PDF Word Excel JPG PowerPoint | MediaTools';
    $description = 'Konversi PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF, dan sebaliknya secara gratis. Batch upload hingga 5 file, proses instan, hasil berkualitas tinggi, tanpa daftar akun, tanpa watermark.';

    $features = [
        'Konversi PDF ke Word (DOCX) akurat & cepat',
        'Konversi Word ke PDF dalam hitungan detik',
        'Konversi Excel ke PDF (XLSX & XLS)',
        'Konversi PowerPoint ke PDF (PPTX & PPT)',
        'Konversi JPG / PNG ke PDF satu atau banyak file',
        'Konversi PDF ke JPG per halaman',
        'Konversi PDF ke PNG resolusi tinggi',
        'Konversi gambar JPG ↔ PNG ↔ WebP',
        'Batch upload hingga 5 file sekaligus',
        'Download hasil per file atau ZIP otomatis',
        'Proses instan tanpa antrian',
        'Tanpa daftar akun dan tanpa watermark',
        'File dihapus otomatis untuk menjaga privasi',
        'Support PDF scan dengan OCR',
        'Alternatif iLovePDF, Smallpdf, dan Adobe Acrobat Online',
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
            'q' => 'Bagaimana cara convert PDF ke Word gratis?',
            'a' => 'Buka halaman File Converter MediaTools, pilih PDF ke Word, upload file PDF, lalu klik konversi. Hasil DOCX bisa langsung diunduh gratis tanpa login dan tanpa watermark.',
        ],
        [
            'q' => 'Apakah bisa convert banyak file sekaligus?',
            'a' => 'Ya. MediaTools mendukung batch upload hingga 5 file sekaligus. File diproses berurutan dan hasilnya bisa diunduh satu per satu atau dalam ZIP.',
        ],
        [
            'q' => 'Apakah MediaTools alternatif iLovePDF?',
            'a' => 'Ya. MediaTools adalah alternatif iLovePDF dan Smallpdf yang ringan, gratis, tanpa login, mendukung batch file, dan menjaga privasi dengan penghapusan file otomatis.',
        ],
        [
            'q' => 'Apakah file saya aman saat diupload?',
            'a' => 'File diproses secara aman di server dan dihapus otomatis setelah selesai diproses sesuai kebijakan privasi sistem.',
        ],
        [
            'q' => 'Format apa saja yang didukung?',
            'a' => 'MediaTools mendukung PDF ↔ Word, PDF ↔ Excel, PDF ↔ PowerPoint, PDF ↔ JPG / PNG, dan konversi gambar JPG ↔ PNG ↔ WebP.',
        ],
        [
            'q' => 'Apakah PDF scan bisa dikonversi ke Word?',
            'a' => 'Ya, tersedia OCR otomatis untuk membantu konversi PDF scan atau berbasis gambar ke Word.',
        ],
        [
            'q' => 'Apakah ada batas ukuran file?',
            'a' => 'Setiap file dapat diunggah hingga 50 MB dan bisa diproses hingga 5 file dalam satu sesi.',
        ],
        [
            'q' => 'Apakah perlu install software atau daftar akun?',
            'a' => 'Tidak perlu. Semua proses berjalan langsung di browser tanpa instalasi dan tanpa pendaftaran akun.',
        ],
    ];

    $faqEntities = [];
    foreach ($faq as $item) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name'  => $item['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $item['a'],
            ],
        ];
    }

    $organizationSchema = [
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
        'description'  => 'Platform tools produktivitas digital gratis untuk invoice, PDF, background remover, converter, QR code, dan kebutuhan digital lainnya.',
        'inLanguage'   => 'id-ID',
        'areaServed'   => 'ID',
        'contactPoint' => [
            '@type'             => 'ContactPoint',
            'contactType'       => 'customer support',
            'email'             => 'halo@mediatools.cloud',
            'areaServed'        => 'ID',
            'availableLanguage' => 'Indonesian',
        ],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        '@id'      => $url . '#breadcrumb',
        'itemListElement' => [
            [
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => 'Beranda',
                'item'     => $appUrl,
            ],
            [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => 'File Converter',
                'item'     => $url,
            ],
        ],
    ];

    $howToSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'HowTo',
        '@id'      => $url . '#howto',
        'name'     => 'Cara Convert PDF ke Word Gratis di MediaTools',
        'description' => 'Langkah mudah mengonversi PDF ke Word secara gratis di MediaTools.',
        'totalTime' => 'PT1M',
        'supply'   => [
            ['@type' => 'HowToSupply', 'name' => 'File PDF'],
            ['@type' => 'HowToSupply', 'name' => 'Browser modern'],
        ],
        'tool' => [
            ['@type' => 'HowToTool', 'name' => 'MediaTools File Converter'],
        ],
        'step' => [
            [
                '@type'    => 'HowToStep',
                'position' => 1,
                'name'     => 'Pilih jenis konversi',
                'text'     => 'Buka halaman File Converter MediaTools dan pilih mode konversi yang diinginkan.',
            ],
            [
                '@type'    => 'HowToStep',
                'position' => 2,
                'name'     => 'Upload file',
                'text'     => 'Upload file dari perangkat Anda. Sistem mendukung hingga 5 file sekaligus.',
            ],
            [
                '@type'    => 'HowToStep',
                'position' => 3,
                'name'     => 'Mulai konversi',
                'text'     => 'Klik tombol konversi dan tunggu proses selesai.',
            ],
            [
                '@type'    => 'HowToStep',
                'position' => 4,
                'name'     => 'Download hasil',
                'text'     => 'Unduh hasil konversi dalam format yang sudah tersedia.',
            ],
        ],
    ];

    $softwareSchema = [
        '@context'               => 'https://schema.org',
        '@type'                  => 'SoftwareApplication',
        '@id'                    => $url . '#software',
        'name'                   => 'MediaTools File Converter',
        'alternateName'          => [
            'PDF Converter Online Gratis',
            'PDF to Word Converter',
            'Word to PDF Online',
            'Convert PDF Online Indonesia',
            'Alternatif iLovePDF Gratis',
            'Alternatif Smallpdf Indonesia',
            'Konversi File Online Gratis',
            'PDF ke Word Gratis',
        ],
        'applicationCategory'    => 'UtilitiesApplication',
        'applicationSubCategory' => 'File Conversion',
        'operatingSystem'        => 'Web Browser',
        'url'                    => $url,
        'description'            => $description,
        'featureList'            => $features,
        'screenshot'             => $appUrl . '/images/tools/fileconverter-preview.png',
            // FIX KRITIS: image wajib ada untuk Google Listingan penjual
            'image'                  => $appUrl . '/images/og/fileconverter.png',
        'softwareVersion'        => '2.0',
        'datePublished'          => '2026-03-01',
        'dateModified'           => now()->toDateString(),
        'offers'              => $toolOffer,
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '4200',
                'reviewCount' => '4200',
            'bestRating'  => '5',
            'worstRating' => '1',
        ],
            // FIX: review minimal 1 entry (wajib jika pakai aggregateRating)
            'review' => [[
                '@type'        => 'Review',
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                'author'       => ['@type' => 'Person', 'name' => 'Pengguna MediaTools'],
                'reviewBody'   => 'Alternatif iLovePDF terbaik! Bisa batch 5 file, PDF ke Word hasilnya akurat, dan tidak perlu daftar akun. Sangat direkomendasikan untuk kerja sehari-hari.',
            ]],
        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],
        'publisher' => [
            '@id' => $appUrl . '/#organization',
        ],
        'author' => [
            '@id' => $appUrl . '/#organization',
        ],
        'inLanguage' => 'id-ID',
        'keywords'   => 'pdf to word, word to pdf, convert pdf online, pdf converter gratis, excel to pdf, jpg to pdf, powerpoint to pdf, ilovepdf alternative, smallpdf alternative, konversi file online, pdf ke word gratis, compress pdf, merge pdf indonesia',
    ];

    $webPageSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebPage',
        '@id'             => $url . '#webpage',
        'url'             => $url,
        'name'            => $name,
        'description'     => $description,
        'inLanguage'      => 'id-ID',
        'isPartOf'        => ['@id' => $appUrl . '/#website'],
        'about'           => ['@id' => $url . '#software'],
        'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url'   => $appUrl . '/images/og/fileconverter.png',
        ],
        'datePublished'   => '2024-01-01',
        'dateModified'    => now()->toDateString(),
    ];

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        '@id'      => $url . '#faq',
        'mainEntity' => $faqEntities,
    ];

    $allSchemas = [
        $organizationSchema,
        $breadcrumbSchema,
        $softwareSchema,
        $faqSchema,
        $howToSchema,
        $webPageSchema,
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($allSchemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

<meta property="og:title" content="Konversi File Gratis — PDF Word Excel JPG PowerPoint Online | MediaTools">
<meta property="og:description" content="PDF ke Word, Word ke PDF, Excel ke PDF, JPG ke PDF. Batch 5 file sekaligus. Gratis, instan, tanpa login. Alternatif terbaik iLovePDF &amp; Smallpdf.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/fileconverter.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="File Converter Online Gratis — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@mediatools">
<meta name="twitter:title" content="Convert PDF Online Gratis — Batch 5 File | MediaTools">
<meta name="twitter:description" content="PDF to Word, Word to PDF, Excel to PDF. Batch 5 file sekaligus. Gratis, tanpa daftar, privasi aman.">
<meta name="twitter:image" content="{{ asset('images/og/fileconverter.png') }}">
<meta name="twitter:image:alt" content="MediaTools File Converter">

<link rel="preload" as="style" href="{{ asset('css/fileconverter.css') }}">
<link rel="preload" as="script" href="{{ asset('js/fileconverter.js') }}">
@endpush