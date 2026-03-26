{{--
    resources/views/seo/bgremover.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: Background Remover
    Target competitor: remove.bg
    Top 10 keywords:
      1. hapus background foto          6. remove bg gratis
      2. remove background              7. background eraser online
      3. background remover             8. foto tanpa background
      4. hapus latar belakang foto      9. remove background photo
      5. background removal online     10. hapus bg foto otomatis
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/bgremover/index.blade.php:

    1. Ganti @section('title') dengan:
       @section('title', 'Hapus Background Foto Gratis — Remove Background Online Otomatis | MediaTools')

    2. Ganti @section('meta_description') dengan:
       @section('meta_description', 'Hapus background foto secara otomatis dengan AI BiRefNet — unggul pada rambut & detail halus. Remove background online gratis, tanpa daftar, download PNG transparan langsung.')

    3. Ganti @section('meta_keywords') dengan:
       @section('meta_keywords', 'hapus background foto, remove background, background remover, hapus latar belakang foto, remove bg, background removal online, remove bg gratis, background eraser online, foto tanpa background, hapus bg foto otomatis, remove background photo, transparent background, background remover ai, hapus background online, remove background free')

    4. Tambahkan baris ini SETELAH @section meta_keywords:
       @include('seo.bgremover')

    5. Pastikan @stack('seo') sudah ada di app.blade.php (lihat app_blade_patch.md)
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'Background Remover — MediaTools',
    'alternateName'       => ['Remove Background Online', 'Hapus Background Foto', 'Remove BG Gratis'],
    'applicationCategory' => 'MultimediaApplication',
    'applicationSubCategory' => 'Image Editing',
    'operatingSystem'     => 'Web, Android, iOS',
    'browserRequirements' => 'Requires JavaScript. Requires HTML5.',
    'url'                 => config('app.url') . '/bg',
    'description'         => 'Hapus background foto secara otomatis dengan AI BiRefNet. Unggul pada rambut & detail halus. Edit manual dengan brush interaktif, download PNG transparan gratis tanpa daftar.',
    'featureList'         => [
        'Hapus background foto otomatis dengan AI',
        'Remove background tanpa daftar',
        'Download PNG transparan',
        'Manual brush editor',
        'Batch processing',
        'Alpha Matting untuk rambut & detail halus',
        'Background kustom warna warni',
    ],
    'screenshot'          => config('app.url') . '/images/tools/bgremover-preview.png',
    'offers'              => [
        '@type'         => 'Offer',
        'price'         => '0',
        'priceCurrency' => 'IDR',
        'availability'  => 'https://schema.org/InStock',
    ],
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.9',
        'ratingCount' => '2340',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
        'logo'  => config('app.url') . '/images/icons-mediatools.png',
    ],
    'inLanguage'  => 'id-ID',
    'keywords'    => 'hapus background foto, remove background, background remover, remove bg, transparent background, hapus latar belakang foto',
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Bagaimana cara hapus background foto secara gratis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Upload foto ke MediaTools Background Remover, AI BiRefNet akan menghapus background secara otomatis dalam hitungan detik. Gratis tanpa perlu daftar akun.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah background remover ini gratis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, 100% gratis tanpa batas penggunaan. Tidak perlu kartu kredit atau daftar akun.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Format gambar apa yang didukung?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Mendukung JPG, PNG, dan WebP hingga 20MB per file. Hasil dapat diunduh dalam format PNG transparan atau JPG dengan background warna.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah foto saya aman dan tidak disimpan?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, semua file diproses di server terenkripsi dan dihapus otomatis setelah proses selesai. Privasi Anda terjaga.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

{{-- Open Graph (spesifik per tool, override OG generik dari layout) --}}
<meta property="og:title"       content="Hapus Background Foto Gratis — Remove Background Online | MediaTools">
<meta property="og:description" content="Hapus background foto otomatis dengan AI BiRefNet. Unggul rambut & detail halus. Gratis tanpa daftar, download PNG transparan.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/bg">
<meta property="og:image"       content="{{ asset('images/og/bgremover.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

{{-- Twitter Card --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Hapus Background Foto Gratis — MediaTools">
<meta name="twitter:description" content="Remove background foto online gratis dengan AI. Tanpa daftar, hasil PNG transparan.">
<meta name="twitter:image"       content="{{ asset('images/og/bgremover.png') }}">

{{-- Canonical (override default layout) --}}
<link rel="canonical" href="{{ config('app.url') }}/bg">

{{-- Hreflang (bahasa Indonesia) --}}
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/bg">
<link rel="alternate" hreflang="x-default" href="{{ config('app.url') }}/bg">
@endpush
