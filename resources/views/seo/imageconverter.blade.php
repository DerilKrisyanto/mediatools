@section('og_image', 'imageconverter')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/imageconverter';

    $name = 'Image Converter Gratis — Resize, Kompres, Convert Gambar | MediaTools';

    $features = [
        'Resize gambar online (custom & preset)',
        'Kompres foto tanpa kehilangan kualitas signifikan',
        'Konversi JPG ↔ PNG ↔ WebP',
        'WebP ke JPG / PNG',
        'Batch proses hingga 10 gambar',
        'Download ZIP otomatis',
        'Lock aspect ratio saat resize',
        'Zero upload — proses 100% di browser',
        'Privasi aman karena file tidak dikirim',
        'Gratis unlimited tanpa login',
    ];

    $faq = [
        [
            'q' => 'Bagaimana cara resize gambar online gratis?',
            'a' => 'Upload gambar, pilih ukuran atau preset, lalu klik proses. Semua dilakukan langsung di browser tanpa upload ke server.',
        ],
        [
            'q' => 'Apakah ini aman dan tanpa upload?',
            'a' => 'Ya, semua proses resize, compress, dan convert terjadi 100% di browser. File tidak pernah dikirim ke server.',
        ],
        [
            'q' => 'Apakah ini alternatif FreeConvert?',
            'a' => 'Ya, MediaTools adalah alternatif FreeConvert yang lebih cepat karena tanpa upload server, lebih aman, dan gratis tanpa batas.',
        ],
        [
            'q' => 'Format apa saja yang didukung?',
            'a' => 'Mendukung JPG, JPEG, PNG, WebP, GIF, dan BMP. Bisa konversi ke JPG, PNG, atau WebP.',
        ],
        [
            'q' => 'Berapa banyak gambar bisa diproses?',
            'a' => 'Hingga 10 gambar sekaligus, dengan opsi download satuan atau ZIP.',
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
        'name'     => 'Cara Resize dan Convert Gambar Online Gratis',
        'description' => 'Langkah mudah resize, kompres, dan convert gambar di MediaTools.',
        'totalTime' => 'PT1M',
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Upload gambar',
                'text' => 'Pilih gambar yang ingin diubah.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Pilih mode',
                'text' => 'Tentukan resize, compress, atau convert format.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Atur output',
                'text' => 'Pilih ukuran, kualitas, dan format hasil.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Download',
                'text' => 'Unduh hasil proses dalam format yang diinginkan.',
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
                'url'    => $appUrl . '/images/icons-mediatools.png',
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
                'Resize Gambar Online',
                'Image Converter Gratis',
                'Kompres Foto Online',
                'JPG to PNG Online',
                'PNG to JPG Converter',
                'JPG to WebP Converter',
                'Image Resize Online',
                'FreeConvert Alternative',
            ],
            'applicationCategory'    => 'MultimediaApplication',
            'applicationSubCategory' => 'Image Processing',
            'operatingSystem'        => 'Web Browser',
            'url'                    => $url,
            'description'            => 'Resize, kompres, dan konversi gambar JPG PNG WebP langsung di browser tanpa upload ke server. Cepat, aman, dan gratis unlimited.',
            'featureList'            => $features,
            'screenshot'             => $appUrl . '/images/tools/imageconverter-preview.png',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'IDR',
                'availability' => 'https://schema.org/InStock',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '2200',
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'provider' => [
                '@id' => $appUrl . '/#organization',
            ],
            'inLanguage' => 'id-ID',
            'keywords'   => 'resize gambar online, kompres foto online, image converter gratis, ubah format gambar, jpg to png, png to jpg, jpg to webp, image resize online, compress gambar gratis',
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
                    'name' => 'Image Converter',
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
            'description'     => 'Resize, kompres, dan convert gambar langsung di browser.',
            'inLanguage'      => 'id-ID',
            'isPartOf'        => ['@id' => $appUrl . '/#website'],
            'about'           => ['@id' => $url . '#software'],
            'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url'   => $appUrl . '/images/og/imageconverter.png',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="Image Converter Gratis — Resize, Kompres, Convert Gambar | MediaTools">
<meta name="description" content="Resize, kompres, dan convert gambar JPG PNG WebP langsung di browser. Tanpa upload, privasi aman, gratis unlimited.">
<meta name="keywords" content="resize gambar online, kompres foto online, image converter gratis, jpg to png, png to jpg, jpg to webp">

<meta property="og:title" content="Resize Kompres & Konversi Gambar Gratis — JPG PNG WebP | MediaTools">
<meta property="og:description" content="Resize, kompres, dan convert gambar langsung di browser. Tanpa upload, privasi aman, gratis unlimited.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/imageconverter.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Image Converter Gratis — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Image Converter Gratis — Resize Kompres JPG PNG WebP">
<meta name="twitter:description" content="Resize, kompres, convert gambar tanpa upload server. Cepat, aman, gratis.">
<meta name="twitter:image" content="{{ asset('images/og/imageconverter.png') }}">
<meta name="twitter:image:alt" content="Image Converter Gratis — MediaTools">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">
@endpush