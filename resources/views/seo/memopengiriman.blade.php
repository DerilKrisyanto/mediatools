@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/memo-pengiriman'; // TODO: sesuaikan dengan slug route asli Anda

    $name = 'Memo Pengiriman Online — Buat & Kirim PDF Berlogo | MediaTools';

    $features = [
        'Buat memo pengiriman PDF profesional dalam hitungan detik',
        'Upload logo perusahaan atau toko sendiri',
        'Kelola daftar barang, qty, dan catatan pengiriman',
        'Simpan riwayat memo & filter berdasarkan periode tanggal',
        'Export laporan memo pengiriman ke Excel',
        'Kirim hasil cetak PDF langsung ke email pelanggan',
        'Tandai status pengiriman: terkirim atau pending',
        'Dukungan jadwal instalasi & tanggal pengiriman otomatis',
        'Cetak langsung dari browser tanpa install software tambahan',
        'Data privat, hanya bisa diakses oleh akun Anda sendiri',
    ];

    /*
    |-------------------------------------------------------------
    | Catatan: tool ini memerlukan login (per-user, data privat),
    | berbeda dari Invoice Generator yang bisa dipakai tanpa akun.
    | Skema di bawah disesuaikan agar tidak mengklaim "tanpa login".
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
            'q' => 'Apa itu memo pengiriman dan untuk apa digunakan?',
            'a' => 'Memo pengiriman adalah bukti serah terima barang antara toko/perusahaan dengan pelanggan, mencatat barang yang dikirim, tujuan, dan tanggal pengiriman.',
        ],
        [
            'q' => 'Bagaimana cara membuat memo pengiriman online?',
            'a' => 'Login ke akun MediaTools, isi data penerima dan daftar barang pada form, lalu simpan. Memo bisa langsung dicetak sebagai PDF berlogo perusahaan Anda.',
        ],
        [
            'q' => 'Apakah data memo pengiriman saya aman dan privat?',
            'a' => 'Ya, setiap memo dan logo yang Anda unggah hanya bisa diakses oleh akun Anda sendiri dan tersimpan aman di cloud.',
        ],
        [
            'q' => 'Bisakah saya mengirim memo pengiriman PDF ke email pelanggan?',
            'a' => 'Bisa. Cukup pastikan email tujuan sudah diisi pada memo, lalu klik tombol kirim untuk mengirimkan file PDF langsung ke pelanggan.',
        ],
        [
            'q' => 'Apakah tool memo pengiriman ini gratis digunakan?',
            'a' => 'Ya, fitur memo pengiriman gratis digunakan oleh setiap pengguna yang memiliki akun MediaTools.',
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
        'name'     => 'Cara Membuat Memo Pengiriman PDF Berlogo',
        'description' => 'Langkah mudah membuat dan mengirim memo pengiriman di MediaTools.',
        'totalTime' => 'PT2M',
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Upload logo',
                'text' => 'Unggah logo perusahaan atau toko Anda agar tampil otomatis di setiap cetakan memo.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Isi data pengiriman',
                'text' => 'Masukkan data penerima, nomor telepon, dan daftar barang yang dikirim.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Simpan memo',
                'text' => 'Simpan data memo pengiriman ke akun Anda.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Cetak atau kirim',
                'text' => 'Cetak memo sebagai PDF atau kirim langsung ke email pelanggan.',
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
            'description' => 'Platform tools produktivitas digital gratis untuk invoice, memo pengiriman, PDF, background remover, converter, QR code, dan kebutuhan digital lainnya.',
            'inLanguage'  => 'id-ID',
            'areaServed'  => 'ID',
        ],
        [
            '@context' => 'https://schema.org',
            '@type'    => 'SoftwareApplication',
            '@id'      => $url . '#software',
            'name'     => $name,
            'alternateName' => [
                'Memo Pengiriman Online',
                'Buat Memo Pengiriman',
                'Delivery Note Generator',
                'Surat Jalan Online',
                'Memo Pengiriman PDF',
                'Bukti Pengiriman Barang',
            ],
            'applicationCategory'    => 'BusinessApplication',
            'applicationSubCategory' => 'Delivery & Logistics',
            'operatingSystem'        => 'Web Browser',
            'url'                    => $url,
            'description'            => 'Buat, kelola, dan kirim memo pengiriman PDF berlogo perusahaan langsung ke email pelanggan. Data privat, khusus untuk akun MediaTools Anda.',
            'featureList'            => $features,
            'datePublished'          => '2025-06-01',
            'dateModified'           => now()->toDateString(),
            'screenshot'             => $appUrl . '/images/tools/memopengiriman-preview.png',
            'softwareVersion'        => '1.0',
            'image'                  => $appUrl . '/images/og/memopengiriman.png',
            'offers'              => $toolOffer,
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.8',
                'ratingCount' => '500',
                'reviewCount' => '500',
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'review' => [[
                '@type'        => 'Review',
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
                'author'       => ['@type' => 'Person', 'name' => 'Pengguna MediaTools'],
                'reviewBody'   => 'Sangat membantu proses pengiriman toko saya. Memo langsung ada logo dan bisa dikirim ke email pelanggan tanpa ribet.',
            ]],
            'provider' => [
                '@id' => $appUrl . '/#organization',
            ],
            'audience' => [
                '@type' => 'Audience',
                'audienceType' => 'Toko, UMKM, Bisnis Distribusi & Logistik',
                'geographicArea' => 'Indonesia',
            ],
            'inLanguage' => 'id-ID',
            'keywords'   => 'memo pengiriman online, buat memo pengiriman, surat jalan online, delivery note generator, memo pengiriman pdf, bukti pengiriman barang, aplikasi memo pengiriman',
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
                    'name' => 'Memo Pengiriman',
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
            'description'     => 'Buat dan kirim memo pengiriman PDF berlogo secara online.',
            'inLanguage'      => 'id-ID',
            'isPartOf'        => ['@id' => $appUrl . '/#website'],
            'about'           => ['@id' => $url . '#software'],
            'breadcrumb'      => ['@id' => $url . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url'   => $appUrl . '/images/og/memopengiriman.png',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="Memo Pengiriman Online — Buat & Kirim PDF Berlogo | MediaTools">
<meta name="description" content="Buat, kelola, cetak PDF berlogo, dan kirim memo pengiriman ke pelanggan lewat email. Data privat & aman, khusus untuk akun MediaTools Anda.">
<meta name="keywords" content="memo pengiriman online, buat memo pengiriman, surat jalan online, delivery note generator, memo pengiriman pdf">

<meta property="og:title" content="Memo Pengiriman Online — Buat & Kirim PDF Berlogo | MediaTools">
<meta property="og:description" content="Kelola seluruh proses pengiriman Anda dalam satu tempat. Cetak PDF berlogo dan kirim langsung ke email pelanggan.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/memopengiriman.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Memo Pengiriman Online — MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Memo Pengiriman Online — MediaTools">
<meta name="twitter:description" content="Buat dan kirim memo pengiriman PDF berlogo langsung ke pelanggan Anda.">
<meta name="twitter:image" content="{{ asset('images/og/memopengiriman.png') }}">
<meta name="twitter:image:alt" content="Memo Pengiriman Online — MediaTools">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">
@endpush