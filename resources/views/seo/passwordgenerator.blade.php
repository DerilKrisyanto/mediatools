@section('og_image', 'passwordgenerator')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/password-generator';

    $name = 'Password Generator Gratis — Buat Kata Sandi Kuat & Aman | MediaTools';

    $features = [
        'Generate password kuat dengan crypto.getRandomValues',
        'Zero server — tidak ada data dikirim ke server',
        'Mode: acak, mudah diingat, dan PIN',
        'Panjang password fleksibel (4–128 karakter)',
        'Filter karakter: huruf besar, kecil, angka, simbol',
        'Hindari karakter mirip (0, O, l, 1, I)',
        'Password strength meter',
        'Bulk generate hingga 20 password',
        '100% gratis tanpa akun',
    ];

    $faq = [
        [
            'q' => 'Apakah password yang dibuat aman?',
            'a' => 'Ya. Password dibuat menggunakan crypto.getRandomValues() yang menghasilkan angka acak kriptografis langsung di browser, tanpa dikirim ke server.',
        ],
        [
            'q' => 'Apakah password disimpan atau dikirim ke server?',
            'a' => 'Tidak. Semua proses berjalan di browser Anda. Tidak ada data yang dikirim atau disimpan.',
        ],
        [
            'q' => 'Bagaimana cara membuat password yang kuat?',
            'a' => 'Gunakan panjang minimal 12–16 karakter dengan kombinasi huruf besar, kecil, angka, dan simbol.',
        ],
        [
            'q' => 'Apakah bisa generate banyak password sekaligus?',
            'a' => 'Ya, fitur bulk generator memungkinkan membuat hingga 20 password sekaligus dalam satu klik.',
        ],
        [
            'q' => 'Apa bedanya dengan password generator lain?',
            'a' => 'MediaTools bekerja langsung di browser tanpa server, lebih privat, lebih cepat, dan tidak membutuhkan akun.',
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
                'Password Generator Gratis',
                'Strong Password Generator',
                'Secure Password Generator Online',
                'Random Password Generator',
                'Generator Kata Sandi Aman',
            ],
            'applicationCategory'    => 'SecurityApplication',
            'applicationSubCategory' => 'Password Generator',
            'operatingSystem'        => 'Web Browser',
            'url'                    => $url,
            'description'            => 'Password generator gratis untuk membuat kata sandi kuat, aman, dan random secara instan. 100% berjalan di browser tanpa server.',
            'featureList'            => $features,
            'screenshot'             => $appUrl . '/images/tools/passwordgenerator-preview.png',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'IDR',
                'availability' => 'https://schema.org/InStock',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '1540',
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'provider' => [
                '@id' => $appUrl . '/#organization',
            ],
            'inLanguage' => 'id-ID',
            'keywords' => 'password generator gratis, buat password kuat, strong password generator, secure password generator, random password generator, password generator online, generator kata sandi, password aman',
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
                    'name' => 'Password Generator',
                    'item' => $url,
                ],
            ],
        ],
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
            'description'     => 'Buat password kuat dan aman langsung di browser.',
            'inLanguage'      => 'id-ID',
            'isPartOf'        => ['@id' => $appUrl . '/#website'],
            'about'           => ['@id' => $url . '#software'],
            'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url'   => $appUrl . '/images/og/passwordgenerator.png',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="Password Generator Gratis — Buat Kata Sandi Kuat & Aman | MediaTools">
<meta name="description" content="Generate password kuat secara instan. 100% di browser, tanpa server, tanpa tracking. Gratis unlimited.">
<meta name="keywords" content="password generator gratis, buat password kuat, strong password generator, secure password generator, random password generator">

<meta property="og:title" content="Password Generator Gratis — Buat Kata Sandi Kuat & Aman | MediaTools">
<meta property="og:description" content="Generate password kuat secara instan. 100% di browser, tanpa server, tanpa tracking. Gratis unlimited.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/passwordgenerator.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Password Generator Gratis — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Strong Password Generator Gratis — MediaTools">
<meta name="twitter:description" content="Buat password kuat & aman tanpa kirim data ke server. Gratis & cepat.">
<meta name="twitter:image" content="{{ asset('images/og/passwordgenerator.png') }}">
<meta name="twitter:image:alt" content="Password Generator Gratis — MediaTools">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">
@endpush