@section('og_image', 'invoice')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/invoice';

    $name = 'Invoice Generator Gratis — Buat Invoice PDF Profesional | MediaTools';

    $features = [
        'Buat invoice PDF profesional dalam 2 menit',
        '3 template: Klasik, Modern, Elegan',
        'Upload logo bisnis atau perusahaan',
        'Kalkulasi subtotal, diskon, dan PPN otomatis',
        'Terbilang bahasa Indonesia otomatis',
        'Tambah item produk atau jasa tanpa batas',
        'Info pembayaran dan rekening bank',
        'Download PDF A4 resolusi tinggi',
        'Tanpa login, langsung pakai',
        'Gratis untuk freelancer dan UMKM',
    ];

    $faq = [
        [
            'q' => 'Bagaimana cara membuat invoice online gratis?',
            'a' => 'Isi data bisnis dan klien, tambahkan item produk atau jasa, lalu klik download PDF. Invoice siap dikirim ke klien tanpa perlu daftar.',
        ],
        [
            'q' => 'Apakah ini benar-benar gratis tanpa batas?',
            'a' => 'Ya, kamu bisa membuat dan download invoice PDF tanpa batas, tanpa akun, dan tanpa biaya.',
        ],
        [
            'q' => 'Apakah cocok untuk freelancer dan UMKM?',
            'a' => 'Sangat cocok. Tool ini dibuat khusus untuk freelancer, UMKM, dan bisnis kecil di Indonesia dengan format invoice profesional.',
        ],
        [
            'q' => 'Apakah bisa pakai PPN dan diskon otomatis?',
            'a' => 'Ya, cukup isi persentase PPN dan diskon. Total dan terbilang akan dihitung otomatis.',
        ],
        [
            'q' => 'Apakah ini alternatif invoice-generator.com?',
            'a' => 'Ya, MediaTools adalah alternatif invoice generator yang lebih sederhana, tanpa login, dan sudah disesuaikan untuk kebutuhan bisnis di Indonesia.',
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
        'name'     => 'Cara Membuat Invoice PDF Profesional Gratis',
        'description' => 'Langkah mudah membuat invoice PDF profesional di MediaTools.',
        'totalTime' => 'PT2M',
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Pilih template',
                'text' => 'Pilih template invoice yang diinginkan.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Isi data',
                'text' => 'Masukkan data bisnis, klien, dan daftar produk atau jasa.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Atur pajak',
                'text' => 'Tambahkan PPN dan diskon jika diperlukan.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Download PDF',
                'text' => 'Unduh invoice dalam format PDF siap kirim.',
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
                'Invoice Generator Gratis',
                'Invoice Maker Free',
                'Buat Invoice Online',
                'Invoice Creator Indonesia',
                'Buat Tagihan Online',
                'Invoice PDF Online',
                'Buat Faktur Online',
            ],
            'applicationCategory'    => 'BusinessApplication',
            'applicationSubCategory' => 'Invoice & Billing',
            'operatingSystem'        => 'Web Browser',
            'url'                    => $url,
            'description'            => 'Buat invoice atau tagihan profesional dalam 2 menit. Download PDF gratis tanpa login. Cocok untuk freelancer dan UMKM Indonesia.',
            'featureList'            => $features,
            'screenshot'             => $appUrl . '/images/tools/invoice-preview.png',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'IDR',
                'availability' => 'https://schema.org/InStock',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '5000',
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'provider' => [
                '@id' => $appUrl . '/#organization',
            ],
            'audience' => [
                '@type' => 'Audience',
                'audienceType' => 'Freelancer, UMKM, Small Business',
                'geographicArea' => 'Indonesia',
            ],
            'inLanguage' => 'id-ID',
            'keywords'   => 'invoice generator gratis, buat invoice, invoice maker free, invoice pdf online, invoice creator, buat tagihan online, template invoice pdf, invoice freelancer, invoice generator indonesia',
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
                    'name' => 'Invoice Generator',
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
            'description'     => 'Buat invoice PDF profesional online gratis.',
            'inLanguage'      => 'id-ID',
            'isPartOf'        => ['@id' => $appUrl . '/#website'],
            'about'           => ['@id' => $url . '#software'],
            'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url'   => $appUrl . '/images/og/invoice.png',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="Invoice Generator Gratis — Buat Invoice PDF Profesional | MediaTools">
<meta name="description" content="Buat invoice profesional dalam 2 menit. Template siap pakai, PPN otomatis, download PDF gratis tanpa login. Cocok untuk freelancer & UMKM Indonesia.">
<meta name="keywords" content="invoice generator gratis, buat invoice, invoice maker free, invoice pdf online, invoice creator, buat tagihan online">

<meta property="og:title" content="Invoice Generator Gratis — Buat Invoice PDF Profesional | MediaTools">
<meta property="og:description" content="Buat invoice profesional dalam 2 menit. Template siap pakai, PPN otomatis, download PDF gratis tanpa login.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/invoice.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Invoice Generator Gratis — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Invoice Generator Gratis — MediaTools">
<meta name="twitter:description" content="Buat invoice PDF profesional gratis. Tanpa login, cepat, siap kirim ke klien.">
<meta name="twitter:image" content="{{ asset('images/og/invoice.png') }}">
<meta name="twitter:image:alt" content="Invoice Generator Gratis — MediaTools">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">
@endpush