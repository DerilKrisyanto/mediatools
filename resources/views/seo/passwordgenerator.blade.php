@section('og_image', 'passwordgenerator')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/password-generator';

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
$name = 'Password Generator — MediaTools';

$features = [
    'Generate password kuat dengan crypto.getRandomValues (browser cryptography)',
    'Zero server — tidak ada data dikirim ke server',
    'Mode: acak, mudah diingat, dan PIN',
    'Panjang password fleksibel (4–128 karakter)',
    'Filter karakter: huruf besar, kecil, angka, simbol',
    'Hindari karakter mirip (0, O, l, 1, I)',
    'Password strength meter (Very Weak → Very Strong)',
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
        'a' => 'Tidak. Semua proses berjalan di browser Anda (client-side). Tidak ada data yang dikirim atau disimpan.',
    ],
    [
        'q' => 'Bagaimana cara membuat password yang kuat?',
        'a' => 'Gunakan panjang minimal 12–16 karakter dengan kombinasi huruf besar, kecil, angka, dan simbol. Hindari kata umum dan gunakan generator acak.',
    ],
    [
        'q' => 'Apakah bisa generate banyak password sekaligus?',
        'a' => 'Ya, fitur bulk generator memungkinkan membuat hingga 20 password sekaligus dalam satu klik.',
    ],
    [
        'q' => 'Apa bedanya dengan LastPass password generator?',
        'a' => 'MediaTools bekerja 100% di browser tanpa server (zero data). Lebih cepat, lebih privat, dan tidak membutuhkan akun.',
    ],
];

/*
|--------------------------------------------------------------------------
| BUILD FAQ SCHEMA
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
| FINAL SCHEMA
|--------------------------------------------------------------------------
*/
$schema = [

    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',

        'name' => $name,

        'alternateName' => [
            'Password Generator Gratis',
            'Strong Password Generator',
            'Secure Password Generator Online',
            'Random Password Generator',
        ],

        'applicationCategory'    => 'SecurityApplication',
        'applicationSubCategory'=> 'Password Generator',
        'operatingSystem'       => 'Web',
        'url'                   => $url,

        'description' => 'Password generator gratis untuk membuat kata sandi kuat, aman, dan random secara instan. 100% berjalan di browser tanpa server (zero data).',

        'featureList' => $features,

        'screenshot' => $appUrl . '/images/tools/passwordgenerator-preview.png',

        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
        ],

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'ratingCount' => '1540',
        ],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'password generator gratis, buat password kuat, strong password generator, secure password generator, random password generator, password generator online, generator kata sandi, password aman',
    ],

    [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faqSchema,
    ],

];

@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Open Graph --}}
<meta property="og:title" content="Password Generator Gratis — Buat Kata Sandi Kuat & Aman | MediaTools">
<meta property="og:description" content="Generate password kuat secara instan. 100% di browser, tanpa server, tanpa tracking. Gratis unlimited.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/passwordgenerator.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Strong Password Generator Gratis — MediaTools">
<meta name="twitter:description" content="Buat password kuat & aman tanpa kirim data ke server. Gratis & cepat.">
<meta name="twitter:image" content="{{ asset('images/og/passwordgenerator.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush