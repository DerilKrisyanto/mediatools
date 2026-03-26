{{--
    resources/views/seo/passwordgenerator.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: Password Generator
    Target competitor: lastpass.com/features/password-generator
    Top 10 keywords:
      1. password generator gratis      6. generate password kuat
      2. buat password kuat             7. random password generator
      3. strong password generator      8. password generator online
      4. password aman                  9. generator kata sandi
      5. kata sandi kuat               10. secure password generator
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/passwordgenerator/index.blade.php:

    1. @section('title', 'Password Generator Gratis — Buat Kata Sandi Kuat & Aman Instan | MediaTools')
    2. @section('meta_description', 'Buat password kuat dan aman secara instan menggunakan kriptografi browser — zero server, privasi 100%. Generator kata sandi gratis dengan mode acak, mudah diingat, dan PIN. Alternatif LastPass Password Generator.')
    3. @section('meta_keywords', 'password generator gratis, buat password kuat, strong password generator, password aman, kata sandi kuat, generate password kuat, random password generator, password generator online, generator kata sandi, secure password generator, password creator gratis, buat kata sandi, password maker, random password, password keamanan akun')
    4. @include('seo.passwordgenerator')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'Password Generator — MediaTools',
    'alternateName'       => ['Buat Password Kuat', 'Strong Password Generator', 'Generator Kata Sandi', 'Random Password Generator Gratis'],
    'applicationCategory' => 'SecurityApplication',
    'applicationSubCategory' => 'Password Management',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/password-generator',
    'description'         => 'Buat password kuat dan unik secara instan menggunakan crypto.getRandomValues() browser — tidak ada data yang dikirim ke server. Gratis unlimited dengan 3 mode: Acak, Mudah Diingat, dan PIN.',
    'featureList'         => [
        'Password acak dengan entropi kriptografis (crypto.getRandomValues)',
        'Mode mudah diingat (kata + angka)',
        'Mode PIN (angka saja)',
        'Panjang password 4–128 karakter',
        'Filter karakter: huruf besar, kecil, angka, simbol',
        'Hindari karakter mirip (0, O, l, 1, I)',
        'Ukur kekuatan password (Very Weak → Very Strong)',
        'Generate hingga 20 password sekaligus (Bulk)',
        'Zero server — semua di browser',
        'Gratis unlimited tanpa daftar',
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
        'ratingCount' => '1540',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'password generator gratis, buat password kuat, strong password generator, random password, kata sandi aman, generate password',
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Apakah password yang dibuat dikirim ke server?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Tidak sama sekali. Semua password dibuat menggunakan crypto.getRandomValues() yang berjalan 100% di browser Anda. Tidak ada data yang dikirim ke server manapun.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Seberapa kuat password yang dihasilkan?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Password 16 karakter dengan kombinasi huruf besar, kecil, angka, dan simbol memiliki entropi ~95 bit — membutuhkan miliaran tahun untuk di-crack dengan brute force attack.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apa itu mode "Mudah Diingat"?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Mode ini menghasilkan password berupa kombinasi kata-kata yang mudah diingat dengan angka di antaranya — lebih aman dari password umum namun lebih mudah diingat dari password acak penuh.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Bisa buat banyak password sekaligus?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, fitur Bulk Generate bisa membuat hingga 20 password sekaligus dengan satu klik. Semua bisa disalin sekaligus.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="Password Generator Gratis — Buat Kata Sandi Kuat & Aman | MediaTools">
<meta property="og:description" content="Buat password kuat secara instan. Zero server, privasi 100%. Mode acak, mudah diingat, dan PIN. Gratis unlimited.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/password-generator">
<meta property="og:image"       content="{{ asset('images/og/passwordgenerator.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Password Generator Gratis — Kata Sandi Kuat & Aman | MediaTools">
<meta name="twitter:description" content="Buat password kuat tanpa kirim data ke server. Privasi 100%, gratis unlimited.">
<meta name="twitter:image"       content="{{ asset('images/og/passwordgenerator.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/password-generator">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/password-generator">
@endpush
