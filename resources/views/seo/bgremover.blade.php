@section('og_image', 'bgremover')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/bg';

    $name = 'Background Remover Gratis — Hapus Background Foto Online | MediaTools';

    $features = [
        'Hapus background foto otomatis dengan AI BiRefNet',
        'Detail rambut & objek halus tetap rapi',
        'Edit manual dengan brush interaktif',
        'Batch upload hingga 20 gambar',
        'Download PNG transparan HD',
        'Custom background warna solid',
        'Before / After slider interaktif',
        'Gratis tanpa daftar akun',
    ];

    $faq = [
        [
            'q' => 'Bagaimana cara hapus background foto secara gratis?',
            'a' => 'Upload foto ke MediaTools Background Remover, AI akan menghapus background otomatis dalam hitungan detik. Gratis tanpa daftar.',
        ],
        [
            'q' => 'Apakah tool ini benar-benar gratis?',
            'a' => 'Ya, 100% gratis tanpa batas penggunaan. Tidak perlu akun atau kartu kredit.',
        ],
        [
            'q' => 'Format gambar apa yang didukung?',
            'a' => 'Mendukung JPG, PNG, dan WebP hingga 20MB. Output PNG transparan atau JPG dengan background warna.',
        ],
        [
            'q' => 'Apakah foto saya aman?',
            'a' => 'Ya, file diproses aman dan otomatis dihapus setelah selesai. Privasi terjaga.',
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
        'name'     => 'Cara Hapus Background Foto Gratis',
        'description' => 'Langkah mudah menghapus background foto secara gratis di MediaTools.',
        'totalTime' => 'PT1M',
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Upload foto',
                'text' => 'Pilih foto yang ingin dihapus background-nya.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Proses AI',
                'text' => 'Tunggu AI menghapus background secara otomatis.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Edit jika perlu',
                'text' => 'Gunakan brush manual untuk merapikan area tertentu.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Download hasil',
                'text' => 'Unduh hasil dalam format PNG transparan HD.',
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
                'Hapus Background Foto',
                'Remove Background Online',
                'Remove BG Gratis',
                'Background Eraser Online',
                'Remove Background AI',
            ],
            'applicationCategory'   => 'MultimediaApplication',
            'applicationSubCategory' => 'Image Editing',
            'operatingSystem'       => 'Web Browser',
            'url'                   => $url,
            'description'           => 'Hapus background foto otomatis dengan AI. Unggul pada rambut dan detail halus. Download PNG transparan gratis tanpa daftar.',
            'featureList'           => $features,
            'screenshot'            => $appUrl . '/images/tools/bgremover-preview.png',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'IDR',
                'availability' => 'https://schema.org/InStock',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '2340',
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'provider' => [
                '@id' => $appUrl . '/#organization',
            ],
            'inLanguage' => 'id-ID',
            'keywords'   => 'hapus background foto, remove background, background remover, remove bg, transparent background, hapus latar belakang foto, remove background ai',
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
                    'name' => 'Background Remover',
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
            'description'     => 'Hapus background foto online gratis dengan AI cepat dan akurat.',
            'inLanguage'      => 'id-ID',
            'isPartOf'        => ['@id' => $appUrl . '/#website'],
            'about'           => ['@id' => $url . '#software'],
            'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url'   => $appUrl . '/images/og/bgremover.png',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="Background Remover Gratis — Hapus Background Foto Online | MediaTools">
<meta name="description" content="Hapus background foto otomatis dengan AI. Detail rambut halus, hasil PNG transparan, gratis tanpa daftar.">
<meta name="keywords" content="hapus background foto, remove background, background remover, remove bg, transparent background">

<meta property="og:title" content="Hapus Background Foto Gratis — Remove Background Online | MediaTools">
<meta property="og:description" content="Hapus background foto otomatis dengan AI. Detail rambut halus, hasil PNG transparan, gratis tanpa daftar.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/bgremover.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Background Remover Gratis — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Hapus Background Foto Gratis — MediaTools">
<meta name="twitter:description" content="Remove background otomatis dengan AI. Gratis, tanpa daftar, hasil HD.">
<meta name="twitter:image" content="{{ asset('images/og/bgremover.png') }}">
<meta name="twitter:image:alt" content="Background Remover Gratis — MediaTools">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">
@endpush