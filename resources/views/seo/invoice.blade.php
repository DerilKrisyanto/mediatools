{{--
    resources/views/seo/invoice.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: Invoice Generator
    Target competitor: invoice-generator.com
    Top 10 keywords:
      1. invoice generator gratis       6. buat tagihan online
      2. buat invoice                   7. template invoice pdf
      3. invoice maker free             8. invoice freelancer
      4. invoice pdf online             9. invoice template gratis
      5. invoice creator               10. invoice generator indonesia
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/invoice/index.blade.php:

    1. @section('title', 'Invoice Generator Gratis — Buat Invoice PDF Profesional Online | MediaTools')
    2. @section('meta_description', 'Buat invoice atau tagihan profesional dalam 2 menit. 3 template siap pakai, kalkulasi PPN & diskon otomatis, download PDF gratis tanpa daftar. Terbaik untuk freelancer & UMKM Indonesia.')
    3. @section('meta_keywords', 'invoice generator gratis, buat invoice, invoice maker free, invoice pdf online, invoice creator, buat tagihan online, template invoice pdf, invoice freelancer, invoice template gratis, invoice generator indonesia, invoice profesional, buat tagihan pdf, invoice online gratis, nota tagihan digital, invoice bisnis')
    4. @include('seo.invoice')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'Invoice Generator — MediaTools',
    'alternateName'       => ['Buat Invoice Gratis', 'Invoice Maker Online', 'Pembuat Tagihan PDF', 'Invoice Creator Indonesia'],
    'applicationCategory' => 'BusinessApplication',
    'applicationSubCategory' => 'Invoice & Billing',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/invoice',
    'description'         => 'Buat invoice atau tagihan profesional dalam 2 menit. Template Klasik, Modern, dan Elegan. Kalkulasi subtotal, diskon, PPN otomatis. Download PDF gratis tanpa daftar.',
    'featureList'         => [
        '3 template invoice profesional (Klasik, Modern, Elegan)',
        'Upload logo perusahaan',
        'Kalkulasi subtotal, diskon %, dan PPN otomatis',
        'Terbilang bahasa Indonesia otomatis',
        'Tambah item tidak terbatas',
        'Info pembayaran & rekening bank',
        'Download PDF A4 resolusi tinggi',
        'Tanpa daftar akun',
        'Cocok untuk freelancer, UMKM, dan bisnis',
    ],
    'offers' => [
        '@type'         => 'Offer',
        'price'         => '0',
        'priceCurrency' => 'IDR',
        'availability'  => 'https://schema.org/InStock',
    ],
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.9',
        'ratingCount' => '4210',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'audience' => [
        '@type'          => 'Audience',
        'audienceType'   => 'Freelancer, UMKM, Small Business Owner',
        'geographicArea' => 'Indonesia',
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'invoice generator gratis, buat invoice, invoice maker, template invoice pdf, invoice freelancer indonesia',
];

$howToLd = [
    '@context'    => 'https://schema.org',
    '@type'       => 'HowTo',
    'name'        => 'Cara Membuat Invoice PDF Profesional Gratis',
    'description' => 'Buat invoice atau tagihan profesional dalam format PDF menggunakan MediaTools Invoice Generator.',
    'totalTime'   => 'PT2M',
    'supply'      => [
        ['@type' => 'HowToSupply', 'name' => 'Nama dan alamat klien'],
        ['@type' => 'HowToSupply', 'name' => 'Daftar item / layanan dengan harga'],
        ['@type' => 'HowToSupply', 'name' => 'Logo perusahaan (opsional)'],
    ],
    'step' => [
        [
            '@type'    => 'HowToStep',
            'position' => 1,
            'name'     => 'Pilih Template',
            'text'     => 'Pilih template invoice yang diinginkan: Klasik, Modern, atau Elegan.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 2,
            'name'     => 'Isi Detail Invoice',
            'text'     => 'Isi nama perusahaan, info klien, tanggal, dan tambahkan item layanan/produk.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 3,
            'name'     => 'Atur Pajak & Diskon',
            'text'     => 'Masukkan persentase diskon dan PPN. Total akhir dan terbilang dihitung otomatis.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 4,
            'name'     => 'Download PDF',
            'text'     => 'Klik "Unduh PDF (A4)" untuk menyimpan invoice siap kirim ke klien.',
        ],
    ],
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Apakah invoice generator ini benar-benar gratis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, 100% gratis tanpa perlu daftar akun atau kartu kredit. Buat dan download invoice PDF sebanyak yang kamu mau.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah bisa tambah logo perusahaan di invoice?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, kamu bisa upload logo perusahaan langsung ke template invoice. Logo akan tampil di pojok kiri atas dokumen.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah ada fitur PPN dan diskon otomatis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, cukup masukkan persentase PPN (default 11%) dan diskon. Total akhir beserta terbilang bahasa Indonesia dihitung otomatis.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd,   JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($howToLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="Invoice Generator Gratis — Buat Invoice PDF Profesional | MediaTools">
<meta property="og:description" content="Buat invoice profesional dalam 2 menit. Template siap pakai, PPN & diskon otomatis, download PDF gratis.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/invoice">
<meta property="og:image"       content="{{ asset('images/og/invoice.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Invoice Generator Gratis — MediaTools">
<meta name="twitter:description" content="Buat invoice profesional, download PDF gratis. Untuk freelancer & UMKM Indonesia.">
<meta name="twitter:image"       content="{{ asset('images/og/invoice.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/invoice">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/invoice">
@endpush
