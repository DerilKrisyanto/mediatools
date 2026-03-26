{{--
    resources/views/seo/mediadownloader.blade.php
    ─────────────────────────────────────────────────────────────────────
    SEO Partial: Media Downloader
    Target competitor: savefrom.net / id.savefrom.net
    Top 10 keywords:
      1. download video youtube         6. download instagram video
      2. youtube downloader gratis      7. youtube to mp3
      3. download tiktok tanpa watermark 8. savefrom alternative
      4. download video tiktok          9. yt downloader online
      5. download video instagram      10. download video online gratis
    ─────────────────────────────────────────────────────────────────────

    CARA PASANG di resources/views/tools/mediadownloader/index.blade.php:

    1. @section('title', 'Download Video YouTube TikTok Instagram Gratis — Tanpa Watermark | MediaTools')
    2. @section('meta_description', 'Download video YouTube, TikTok tanpa watermark, dan Instagram gratis. Cukup paste URL, pilih format MP4 atau MP3, langsung download kualitas hingga 1080p. Alternatif terbaik SaveFrom.net.')
    3. @section('meta_keywords', 'download video youtube, youtube downloader gratis, download tiktok tanpa watermark, download video tiktok, download video instagram, youtube to mp3, savefrom alternative, yt downloader online, download video online gratis, youtube mp3 downloader, download reels instagram, download tiktok video, youtube video downloader, download video shorts, savefrom')
    4. @include('seo.mediadownloader')
--}}

@push('seo')
@php
$jsonLd = [
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => 'Media Downloader — MediaTools',
    'alternateName'       => ['YouTube Downloader Gratis', 'Download TikTok Tanpa Watermark', 'Download Video Instagram', 'SaveFrom Alternative'],
    'applicationCategory' => 'MultimediaApplication',
    'applicationSubCategory' => 'Video Downloader',
    'operatingSystem'     => 'Web',
    'url'                 => config('app.url') . '/media-downloader',
    'description'         => 'Download video YouTube (MP4/MP3), TikTok tanpa watermark, dan Instagram Reels gratis. Paste URL, pilih format, download langsung. Kualitas hingga 1080p FHD.',
    'featureList'         => [
        'Download YouTube MP4 hingga 1080p FHD',
        'Download YouTube audio MP3 kualitas tinggi',
        'Download TikTok video tanpa watermark',
        'Download Instagram Reels dan video post',
        'Download Twitter/X video dan GIF',
        'Download Reddit video',
        'Download Pinterest video',
        'Tanpa daftar akun',
        'Tanpa watermark tambahan',
        'Mendukung 20+ platform video',
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
        'ratingCount' => '5630',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'provider' => [
        '@type' => 'Organization',
        'name'  => 'MediaTools',
        'url'   => config('app.url'),
    ],
    'inLanguage' => 'id-ID',
    'keywords'   => 'download video youtube, youtube downloader gratis, download tiktok tanpa watermark, download instagram video, youtube to mp3',
];

$faqLd = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [
        [
            '@type'          => 'Question',
            'name'           => 'Bagaimana cara download video YouTube gratis?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Salin URL video YouTube, tempel di kolom input MediaTools Media Downloader, pilih format MP4 atau MP3, pilih kualitas (144p hingga 1080p), lalu klik Download. Gratis tanpa daftar.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Apakah bisa download TikTok tanpa watermark?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Ya, pilih tab TikTok, aktifkan opsi "Tanpa Watermark", paste URL video TikTok, lalu download. Hasil video bebas dari logo TikTok.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Platform apa saja yang didukung?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Mendukung YouTube (video, Shorts, Music), TikTok, Instagram (Reels, foto, video), Twitter/X, Reddit, Pinterest, SoundCloud, Vimeo, Dailymotion, dan 20+ platform lainnya.',
            ],
        ],
        [
            '@type'          => 'Question',
            'name'           => 'Berapa kualitas tertinggi yang bisa didownload dari YouTube?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => 'Tersedia kualitas 144p, 360p, 720p HD, dan 1080p Full HD. Kualitas tertinggi bergantung pada video aslinya.',
            ],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>

<meta property="og:title"       content="Download Video YouTube TikTok Instagram Gratis | MediaTools">
<meta property="og:description" content="Download video YouTube 1080p, TikTok tanpa watermark, Instagram Reels. Paste URL, langsung download. Gratis.">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ config('app.url') }}/media-downloader">
<meta property="og:image"       content="{{ asset('images/og/mediadownloader.png') }}">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="YouTube Downloader Gratis — TikTok Instagram | MediaTools">
<meta name="twitter:description" content="Download video YouTube, TikTok tanpa watermark, Instagram Reels. Gratis, instan, tanpa daftar.">
<meta name="twitter:image"       content="{{ asset('images/og/mediadownloader.png') }}">

<link rel="canonical" href="{{ config('app.url') }}/media-downloader">
<link rel="alternate" hreflang="id" href="{{ config('app.url') }}/media-downloader">
@endpush
