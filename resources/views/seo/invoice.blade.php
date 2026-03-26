{{-- resources/views/seo/invoice.blade.php --}}

@section('og_image', 'invoice')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/invoice';

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
$name = 'Invoice Generator — MediaTools';

$features = [
    'Buat invoice PDF profesional dalam 2 menit',
    '3 template: Klasik, Modern, Elegan',
    'Upload logo bisnis / perusahaan',
    'Kalkulasi subtotal, diskon & PPN otomatis',
    'Terbilang bahasa Indonesia otomatis',
    'Tambah item produk / jasa tanpa batas',
    'Info pembayaran & rekening bank',
    'Download PDF A4 resolusi tinggi',
    'Tanpa login, langsung pakai',
    'Gratis untuk freelancer & UMKM',
];

$faq = [
    [
        'q' => 'Bagaimana cara membuat invoice online gratis?',
        'a' => 'Isi data bisnis dan klien, tambahkan item produk atau jasa, lalu klik download PDF. Invoice langsung siap dikirim ke klien tanpa perlu daftar.',
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

/*
|--------------------------------------------------------------------------
| FAQ SCHEMA
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| HOW TO
|--------------------------------------------------------------------------
*/
$howToSchema = [
    '@type' => 'HowTo',
    'name' => 'Cara Membuat Invoice PDF Profesional Gratis',
    'totalTime' => 'PT2M',
    'step' => [
        [
            '@type' => 'HowToStep',
            'position' => 1,
            'name' => 'Pilih template',
            'text' => 'Pilih template invoice yang diinginkan (Klasik, Modern, Elegan).',
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
            'text' => 'Download invoice dalam format PDF siap kirim.',
        ],
    ],
];

/*
|--------------------------------------------------------------------------
| FINAL SCHEMA
|--------------------------------------------------------------------------
*/
$schema = [

    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',
        'name'     => $name,
        'alternateName' => [
            'Invoice Generator Gratis',
            'Invoice Maker Free',
            'Buat Invoice Online',
            'Invoice Creator Indonesia',
            'Buat Tagihan Online',
            'Invoice PDF Online',
        ],
        'applicationCategory'    => 'BusinessApplication',
        'applicationSubCategory'=> 'Invoice & Billing',
        'operatingSystem'       => 'Web',
        'url'                   => $url,
        'description'           => 'Buat invoice atau tagihan profesional dalam 2 menit. Download PDF gratis tanpa login. Cocok untuk freelancer dan UMKM Indonesia.',
        'featureList'           => $features,
        'screenshot'            => $appUrl . '/images/tools/invoice-preview.png',
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
        ],
        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
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
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faqSchema,
    ],

    array_merge(['@context' => 'https://schema.org'], $howToSchema),

];

@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Open Graph --}}
<meta property="og:title" content="Invoice Generator Gratis — Buat Invoice PDF Profesional | MediaTools">
<meta property="og:description" content="Buat invoice profesional dalam 2 menit. Template siap pakai, PPN otomatis, download PDF gratis tanpa login. Cocok untuk freelancer & UMKM Indonesia.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/invoice.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Invoice Generator Gratis — MediaTools">
<meta name="twitter:description" content="Buat invoice PDF profesional gratis. Tanpa login, cepat, siap kirim ke klien.">
<meta name="twitter:image" content="{{ asset('images/og/invoice.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush