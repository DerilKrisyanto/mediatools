@section('og_image', 'mediadownloader')

@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');
$url    = $appUrl . '/media-downloader';

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
$name = 'Media Downloader — MediaTools';

$features = [
    'Download video YouTube hingga 1080p Full HD',
    'Convert YouTube ke MP3 kualitas tinggi',
    'Download TikTok tanpa watermark',
    'Download Instagram Reels, video, dan foto',
    'Download Twitter/X, Reddit, Pinterest video',
    'Support YouTube Shorts & TikTok HD',
    'Tanpa watermark tambahan',
    'Tanpa daftar akun, langsung download',
    'Mendukung 20+ platform video populer',
];

$faq = [
    [
        'q' => 'Bagaimana cara download video YouTube gratis?',
        'a' => 'Salin URL video YouTube, tempel di MediaTools Media Downloader, pilih format MP4 atau MP3, lalu klik download. Tidak perlu daftar.',
    ],
    [
        'q' => 'Apakah bisa download TikTok tanpa watermark?',
        'a' => 'Ya, cukup paste URL video TikTok dan pilih opsi tanpa watermark. Video akan diunduh tanpa logo.',
    ],
    [
        'q' => 'Apakah bisa convert YouTube ke MP3?',
        'a' => 'Bisa. Pilih format MP3 setelah memasukkan URL YouTube untuk download audio kualitas tinggi.',
    ],
    [
        'q' => 'Platform apa saja yang didukung?',
        'a' => 'Mendukung YouTube, TikTok, Instagram, Twitter/X, Reddit, Pinterest, Vimeo, dan lebih dari 20 platform lainnya.',
    ],
    [
        'q' => 'Apakah layanan ini gratis?',
        'a' => 'Ya, 100% gratis tanpa batas penggunaan dan tanpa perlu akun.',
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
        'name'     => $name,
        'alternateName' => [
            'YouTube Downloader Gratis',
            'Download TikTok Tanpa Watermark',
            'Download Video Instagram',
            'SaveFrom Alternative',
        ],
        'applicationCategory'    => 'MultimediaApplication',
        'applicationSubCategory'=> 'Video Downloader',
        'operatingSystem'       => 'Web',
        'url'                   => $url,
        'description'           => 'Download video YouTube (MP4/MP3), TikTok tanpa watermark, dan Instagram gratis. Paste URL dan download langsung kualitas hingga 1080p.',
        'featureList'           => $features,
        'screenshot'            => $appUrl . '/images/tools/mediadownloader-preview.png',

        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
        ],

        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.8',
            'ratingCount' => '5630',
        ],

        'provider' => [
            '@type' => 'Organization',
            'name'  => 'MediaTools',
            'url'   => $appUrl,
        ],

        'inLanguage' => 'id-ID',

        'keywords' => 'download video youtube, youtube downloader gratis, youtube to mp3, download tiktok tanpa watermark, download video instagram, download reels instagram, download video online gratis, yt downloader, savefrom alternative',
    ],

    [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faqSchema,
    ],

];

@endphp

{{-- JSON-LD --}}
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Open Graph --}}
<meta property="og:title" content="Download Video YouTube TikTok Instagram Gratis — Tanpa Watermark | MediaTools">
<meta property="og:description" content="Download video YouTube 1080p, TikTok tanpa watermark, Instagram Reels. Gratis, cepat, tanpa daftar.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/mediadownloader.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="YouTube Downloader Gratis — MediaTools">
<meta name="twitter:description" content="Download video YouTube, TikTok tanpa watermark, Instagram Reels. Gratis tanpa daftar.">
<meta name="twitter:image" content="{{ asset('images/og/mediadownloader.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

@endpush