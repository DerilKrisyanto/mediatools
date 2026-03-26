@section('og_image', 'signature')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/signature';

$name = 'Email Signature Generator — MediaTools';

/*
|--------------------------------------------------------------------------
| FEATURES (SEO BOOST)
|--------------------------------------------------------------------------
*/
$features = [
    '3 template signature profesional: Klasik, Modern, Elegan',
    'Custom warna sesuai brand bisnis',
    'Upload foto profil atau logo perusahaan',
    'Input lengkap: nama, jabatan, perusahaan, kontak, website, LinkedIn',
    'Copy HTML untuk Gmail, Outlook, Apple Mail',
    'Download PNG signature',
    'Live preview realtime',
    'Kompatibel semua email client (inline CSS)',
    'Gratis unlimited tanpa watermark',
    'Cocok untuk freelancer, bisnis, corporate, startup',
];

/*
|--------------------------------------------------------------------------
| FAQ EXPANSION (CTR BOOST)
|--------------------------------------------------------------------------
*/
$faq = [
    [
        'q' => 'Bagaimana cara membuat email signature profesional?',
        'a' => 'Isi data seperti nama, jabatan, dan kontak, pilih template, lalu salin HTML signature untuk digunakan di Gmail atau Outlook.',
    ],
    [
        'q' => 'Apakah email signature bisa digunakan di Gmail dan Outlook?',
        'a' => 'Ya, signature menggunakan HTML inline sehingga kompatibel dengan Gmail, Outlook, Apple Mail, dan semua email client.',
    ],
    [
        'q' => 'Kenapa email signature penting untuk bisnis?',
        'a' => 'Email signature membantu membangun branding, meningkatkan kepercayaan, dan memberikan informasi kontak secara profesional di setiap email.',
    ],
    [
        'q' => 'Apakah HTML email signature aman digunakan?',
        'a' => 'Ya, HTML signature hanya berupa tampilan visual dan tidak mengandung script berbahaya.',
    ],
    [
        'q' => 'Apakah bisa digunakan di HP?',
        'a' => 'Bisa, namun pemasangan biasanya lebih mudah dilakukan melalui desktop Gmail atau Outlook.',
    ],
];

/*
|--------------------------------------------------------------------------
| BUILD FAQ
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
$howTo = [
    '@type' => 'HowTo',
    'name'  => 'Cara Membuat Email Signature Profesional',
    'totalTime' => 'PT3M',
    'step' => [
        [
            '@type' => 'HowToStep',
            'position' => 1,
            'name' => 'Isi Data',
            'text' => 'Masukkan nama, jabatan, perusahaan, email, dan kontak.',
        ],
        [
            '@type' => 'HowToStep',
            'position' => 2,
            'name' => 'Pilih Template',
            'text' => 'Pilih desain signature sesuai gaya brand Anda.',
        ],
        [
            '@type' => 'HowToStep',
            'position' => 3,
            'name' => 'Copy HTML',
            'text' => 'Salin HTML signature untuk digunakan.',
        ],
        [
            '@type' => 'HowToStep',
            'position' => 4,
            'name' => 'Pasang di Email',
            'text' => 'Tempel HTML ke pengaturan signature Gmail atau Outlook.',
        ],
    ],
];

/*
|--------------------------------------------------------------------------
| FINAL SCHEMA (MERGED)
|--------------------------------------------------------------------------
*/
$schema = [

    [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',

        'name' => $name,

        'alternateName' => [
            'Email Signature Generator Gratis',
            'Signature Email Profesional',
            'Gmail Signature Maker',
            'HTML Email Signature Builder',
        ],

        'applicationCategory'    => 'BusinessApplication',
        'applicationSubCategory'=> 'Email Signature Generator',
        'operatingSystem'       => 'Web',
        'url'                   => $url,

        'description' => 'Buat email signature profesional untuk bisnis dan personal. Template modern, custom logo, dan kompatibel Gmail & Outlook.',

        'featureList' => $features,

        'screenshot' => $appUrl . '/images/tools/signature-preview.png',

        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
        ],

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.8',
            'ratingCount' => '1920',
        ],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'email signature gratis, email signature generator, html email signature, gmail signature template, outlook email signature, professional email signature',
    ],

    [
        '@context' => 'https://schema.org',
        '@type'    => 'HowTo',
    ] + $howTo,

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
<meta property="og:title" content="Email Signature Generator Gratis — Professional Email Signature">
<meta property="og:description" content="Buat email signature profesional untuk bisnis. Template modern, logo, dan HTML siap pakai.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/signature.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Email Signature Generator Gratis — MediaTools">
<meta name="twitter:description" content="Buat signature email profesional. Gmail & Outlook ready. Copy HTML instan.">
<meta name="twitter:image" content="{{ asset('images/og/signature.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush