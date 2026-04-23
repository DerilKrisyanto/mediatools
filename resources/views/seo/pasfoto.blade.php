@section('og_image', 'pasfoto')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/pasfoto';

    $name = 'Smart Photo Studio — Pas Foto Online & Background Remover | MediaTools';

    $toolOffer = [
        '@type'           => 'Offer',
        'price'           => '0',
        'priceCurrency'   => 'IDR',
        'availability'    => 'https://schema.org/InStock',
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

    $schema = [
        '@context'            => 'https://schema.org',
        '@type'               => 'SoftwareApplication',
        'name'                => $name,
        'url'                 => $url,
        'image'               => $appUrl . '/images/og/home.png',
        'description'         => 'Buat pas foto online & hapus background dengan AI BiRefNet. Ukuran 2×3, 3×4, 4×6. Background merah, biru, putih. Export JPG & PDF. Gratis tanpa daftar.',
        'applicationCategory' => 'UtilitiesApplication',
        'operatingSystem'     => 'Web Browser',
        'offers'              => $toolOffer,
        'aggregateRating'     => ['@type'=>'AggregateRating','ratingValue'=>'4.9','ratingCount'=>'5000','reviewCount'=>'5000','bestRating'=>'5','worstRating'=>'1'],
        'review'              => [['@type'=>'Review','reviewRating'=>['@type'=>'Rating','ratingValue'=>'5','bestRating'=>'5'],'author'=>['@type'=>'Person','name'=>'Pengguna MediaTools'],'reviewBody'=>'Sangat mudah digunakan untuk membuat pas foto profesional dengan background yang bisa dipilih.']],
        'author'              => ['@id' => $appUrl . '/#organization'],
    ];

    $faqSchema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => [
            ['@type'=>'Question','name'=>'Berapa ukuran pas foto 2x3, 3x4, dan 4x6?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Pas foto 2x3 berukuran 2×3 cm, pas foto 3x4 berukuran 3×4 cm, dan pas foto 4x6 berukuran 4×6 cm. Ini adalah standar dokumen resmi Indonesia.']],
            ['@type'=>'Question','name'=>'Apakah foto saya dikirim ke server?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Tidak. Semua proses dilakukan sepenuhnya di browser Anda menggunakan JavaScript. Foto Anda tidak pernah diunggah ke server kami.']],
            ['@type'=>'Question','name'=>'Background apa untuk pas foto CPNS?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Untuk pas foto CPNS, background yang umum digunakan adalah merah atau biru. Selalu cek persyaratan instansi yang Anda tuju karena aturan bisa berbeda.']],
            ['@type'=>'Question','name'=>'Berapa ukuran file pas foto untuk upload dokumen online?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Sebagian besar portal membatasi 100KB–300KB. Gunakan fitur kompres di Smart Photo Studio untuk mengatur ukuran file.']],
        ],
    ];
@endphp

<meta name="title"    content="{{ $name }}">
<meta name="keywords" content="pas foto online gratis, buat pas foto 3x4, pas foto 2x3 online, pas foto background merah biru, smart photo studio, pas foto cpns online, photo studio online gratis">
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id"        href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

<meta property="og:title"       content="{{ $name }}">
<meta property="og:description" content="Buat pas foto online gratis: 2×3, 3×4, 4×6. Background merah/biru/putih. AI Background Remover. Export JPG & PDF. Tanpa daftar.">
<meta property="og:image"       content="{{ $appUrl }}/images/og/home.png">

<meta name="twitter:title"       content="{{ $name }}">
<meta name="twitter:description" content="Pas foto online gratis — background merah/biru/putih, AI remover, export PDF. Langsung di browser.">

<script type="application/ld+json">
{!! json_encode([$schema, $faqSchema], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush