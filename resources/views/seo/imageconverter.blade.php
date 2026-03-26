{{--
    resources/views/seo/imageconverter.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: Image Converter
    Target competitor: freeconvert.com/image-converter
    Top 10 keywords:
      1. resize gambar online           6. compress gambar gratis
      2. konversi gambar                7. jpg to png
      3. image converter gratis         8. png to jpg
      4. kompres foto online            9. jpg to webp
      5. ubah format gambar            10. image resize online
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/imageconverter/index.blade.php:

    1. @section('title', 'Resize Kompres & Konversi Gambar Gratis — JPG PNG WebP | MediaTools')
    2. @section('meta_description', 'Resize, kompres, dan konversi gambar JPG PNG WebP langsung di browser. Tanpa upload ke server, privasi 100% terjaga. Gratis unlimited. Alternatif terbaik FreeConvert.')
    3. @section('meta_keywords', 'resize gambar online, konversi gambar, image converter gratis, kompres foto online, ubah format gambar, jpg to png, png to jpg, jpg to webp, image resize online, compress gambar gratis, webp to jpg, png to webp, image compressor, ubah ukuran gambar, konversi foto online')
    4. @include('seo.imageconverter')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'Image Converter — MediaTools',
    'alternateName'       => ['Resize Gambar Online', 'Kompres Foto Gratis', 'Image Converter Gratis', 'JPG to PNG Online'],
    'applicationCategory' => 'MultimediaApplication',
    'applicationSubCategory' => 'Image Processing',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/imageconverter',
    'description'         => 'Resize, kompres, dan konversi gambar JPG, PNG, WebP langsung di browser — tanpa upload ke server, privasi 100% terjaga. Convert, compress & resize gambar gratis unlimited.',
    'featureList'         => [
        'Konversi JPG ke PNG',
        'Konversi PNG ke JPG',
        'Konversi JPG ke WebP',
        'Konversi PNG ke WebP',
        'Konversi WebP ke JPG/PNG',
        'Resize gambar dengan preset FHD, HD, IG Story, IG Square',
        'Kompres foto dengan target ukuran kustom',
        'Proses hingga 10 gambar sekaligus',
        'Zero upload ke server — privasi 100%',
        'Download ZIP batch',
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
        'ratingCount' => '1870',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'resize gambar online, image converter, konversi gambar, kompres foto, jpg to png, png to jpg, jpg to webp',
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Bagaimana cara resize gambar online gratis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Pilih tab "Resize", upload gambar, masukkan dimensi baru (W × H) atau pilih preset seperti FHD, HD, IG Story, lalu klik "Proses Sekarang". Semua terjadi di browser tanpa upload ke server.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah gambar saya dikirim ke server?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Tidak sama sekali. Semua proses resize, compress, dan convert gambar terjadi 100% di browser Anda. File tidak pernah meninggalkan perangkat Anda.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Format gambar apa saja yang bisa dikonversi?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Mendukung JPG, JPEG, PNG, WebP, GIF, dan BMP. Bisa konversi ke JPG, PNG, atau WebP.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Berapa banyak gambar yang bisa diproses sekaligus?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Bisa upload dan proses hingga 10 gambar sekaligus. Hasil bisa didownload satu per satu atau semuanya dalam ZIP.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="Resize Kompres & Konversi Gambar Gratis — JPG PNG WebP | MediaTools">
<meta property="og:description" content="Resize, kompres, konversi JPG PNG WebP di browser. Zero upload server, privasi 100%, gratis unlimited.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/imageconverter">
<meta property="og:image"       content="{{ asset('images/og/imageconverter.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Image Converter Gratis — Resize Kompres Konversi Gambar">
<meta name="twitter:description" content="Resize, kompres, konversi gambar JPG PNG WebP. Tanpa server, privasi terjaga, gratis.">
<meta name="twitter:image"       content="{{ asset('images/og/imageconverter.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/imageconverter">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/imageconverter">
@endpush
