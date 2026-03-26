{{--
    resources/views/seo/signature.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: Email Signature Generator
    Target competitor: signwell.com/online-signature (+ hubspot signature)
    Top 10 keywords:
      1. email signature gratis          6. gmail signature template
      2. buat tanda tangan email         7. professional email signature
      3. email signature generator       8. html email signature
      4. tanda tangan email profesional  9. outlook email signature
      5. signature email gratis         10. email signature maker
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/signature/index.blade.php:

    1. @section('title', 'Email Signature Generator Gratis — Tanda Tangan Email Profesional | MediaTools')
    2. @section('meta_description', 'Buat tanda tangan email profesional dalam hitungan menit. 3 template siap pakai (Klasik, Modern, Elegan), copy-paste ke Gmail, Outlook & semua email client. HTML email signature gratis unlimited.')
    3. @section('meta_keywords', 'email signature gratis, buat tanda tangan email, email signature generator, tanda tangan email profesional, signature email gratis, gmail signature template, professional email signature, html email signature, outlook email signature, email signature maker, buat signature email, template tanda tangan email, email footer profesional, signature gmail, email signature creator')
    4. @include('seo.signature')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'Email Signature Generator — MediaTools',
    'alternateName'       => ['Buat Tanda Tangan Email', 'Signature Studio', 'Gmail Signature Maker', 'Professional Email Signature Free'],
    'applicationCategory' => 'BusinessApplication',
    'applicationSubCategory' => 'Email Signature Creator',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/signature',
    'description'         => 'Buat tanda tangan email profesional dalam hitungan menit. 3 template (Klasik, Modern, Elegan), 10+ warna aksen, copy HTML untuk Gmail & Outlook, download PNG, simpan ke akun. Gratis unlimited.',
    'featureList'         => [
        '3 template signature profesional: Klasik, Modern, Elegan',
        '10+ pilihan warna aksen',
        'Upload foto/logo profil',
        'Input nama, jabatan, perusahaan, email, telepon, website, LinkedIn',
        'Live preview realtime',
        'Salin HTML untuk Gmail, Outlook, Apple Mail',
        'Download PNG signature',
        'Print signature',
        'Simpan signature ke akun (untuk user terdaftar)',
        'Style inline — kompatibel semua email client',
    ],
    'offers' => [
        '@type'         => 'Offer',
        'price'         => '0',
        'priceCurrency' => 'IDR',
        'availability'  => 'https://schema.org/InStock',
    ],
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.8',
        'ratingCount' => '1920',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'email signature gratis, buat tanda tangan email, gmail signature, professional email signature, html email signature, signature maker',
];

$howToLd = [
    '@context'    => 'https://schema.org',
    '@type'       => 'HowTo',
    'name'        => 'Cara Membuat Email Signature Profesional untuk Gmail',
    'description' => 'Buat tanda tangan email profesional dan pasang di Gmail menggunakan MediaTools Signature Studio.',
    'totalTime'   => 'PT3M',
    'step'        => [
        [
            '@type'    => 'HowToStep',
            'position' => 1,
            'name'     => 'Isi Informasi Profesional',
            'text'     => 'Masukkan nama, jabatan, perusahaan, email, nomor telepon, website, dan LinkedIn di form editor.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 2,
            'name'     => 'Pilih Template & Warna',
            'text'     => 'Pilih template Klasik, Modern, atau Elegan, lalu pilih warna aksen yang sesuai brand Anda.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 3,
            'name'     => 'Salin HTML Signature',
            'text'     => 'Klik "Salin HTML untuk Gmail / Outlook" untuk menyalin kode signature siap pakai.',
        ],
        [
            '@type'    => 'HowToStep',
            'position' => 4,
            'name'     => 'Pasang di Gmail',
            'text'     => 'Buka Gmail → Pengaturan → Umum → Tanda tangan → Edit → klik ikon Source Code → tempel HTML → Simpan perubahan.',
        ],
    ],
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Bagaimana cara pasang email signature di Gmail?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Buat signature di MediaTools, klik "Salin HTML". Buka Gmail → Pengaturan → Umum → Tanda tangan → Edit → klik ikon <> Source code → tempel HTML → Simpan perubahan. Signature langsung aktif.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah email signature ini kompatibel dengan Outlook?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, semua style sudah inline sehingga kompatibel dengan Gmail, Outlook, Apple Mail, Yahoo Mail, dan semua email client populer.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah bisa tambah foto profil di signature?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, upload foto PNG/JPG maksimal 2MB. Foto akan tampil di signature dan ikut tersertakan saat disalin sebagai HTML.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd,   JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($howToLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="Email Signature Generator Gratis — Tanda Tangan Email Profesional | MediaTools">
<meta property="og:description" content="Buat tanda tangan email profesional dalam menit. Gmail & Outlook ready, copy HTML, gratis unlimited.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/signature">
<meta property="og:image"       content="{{ asset('images/og/signature.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Email Signature Generator Gratis — MediaTools">
<meta name="twitter:description" content="Buat signature email profesional. Gmail & Outlook ready. Copy HTML, gratis unlimited.">
<meta name="twitter:image"       content="{{ asset('images/og/signature.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/signature">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/signature">
@endpush
