@push('seo')
@php

$appUrl = rtrim(config('app.url'), '/');

/*
|--------------------------------------------------------------------------
| MASTER DATA TOOLS (Single Source of Truth)
|--------------------------------------------------------------------------
| Semua data tools hanya didefinisikan di sini.
| Dipakai ulang untuk:
| - ItemList
| - OfferCatalog
|--------------------------------------------------------------------------
*/
$tools = [
    ['name' => 'Background Remover Gratis', 'slug' => 'bg', 'desc' => 'Hapus background foto otomatis dengan AI.'],
    ['name' => 'PDF to Word & File Converter', 'slug' => 'file-converter', 'desc' => 'Konversi PDF ke Word, Excel, JPG.'],
    ['name' => 'Image Converter Resize Compress', 'slug' => 'imageconverter', 'desc' => 'Resize, kompres, konversi gambar JPG PNG WebP.'],
    ['name' => 'Invoice Generator Gratis', 'slug' => 'invoice', 'desc' => 'Buat invoice PDF profesional dalam 2 menit.'],
    ['name' => 'LinkTree Builder Link in Bio', 'slug' => 'linktree', 'desc' => 'Buat halaman bio link untuk sosial media.'],
    ['name' => 'Media Downloader YouTube TikTok', 'slug' => 'media-downloader', 'desc' => 'Download video tanpa watermark.'],
    ['name' => 'Password Generator Kuat', 'slug' => 'password-generator', 'desc' => 'Buat password aman dan kuat.'],
    ['name' => 'PDF Utilities Merge Split Compress', 'slug' => 'pdfutilities', 'desc' => 'Gabung, pisah, kompres PDF.'],
    ['name' => 'QR Code Generator Custom', 'slug' => 'qr', 'desc' => 'Buat QR Code custom bisnis.'],
    ['name' => 'Email Signature Generator', 'slug' => 'signature', 'desc' => 'Tanda tangan email profesional.'],
];

/*
|--------------------------------------------------------------------------
| GENERATE ItemList & OfferCatalog (No Duplicate Logic)
|--------------------------------------------------------------------------
*/
$itemList = [];
$offerList = [];

foreach ($tools as $i => $tool) {
    $url = $appUrl . '/' . $tool['slug'];

    $itemList[] = [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => $tool['name'],
        'url'      => $url,
        'description' => $tool['desc'],
    ];

    $offerList[] = [
        '@type' => 'Offer',
        'itemOffered' => [
            '@type' => 'SoftwareApplication',
            'name'  => $tool['name'],
            'url'   => $url,
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| STRUCTURED DATA
|--------------------------------------------------------------------------
*/

$schema = [

    // Organization
    [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'MediaTools',
        'url'      => $appUrl,
        'logo'     => $appUrl . '/images/icons-mediatools.png',
        'description' => 'Platform tools produktivitas digital gratis untuk UMKM, freelancer, dan creator Indonesia.',
        'foundingDate' => '2026',
        'areaServed'   => 'Indonesia',
        'inLanguage'   => 'id-ID',
        'sameAs' => ['https://www.instagram.com/deril_krisyanto'],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'email' => 'halo@mediatools.cloud',
            'contactType' => 'customer support',
            'availableLanguage' => 'Indonesian',
        ],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name'  => 'MediaTools Tools Gratis',
            'itemListElement' => $offerList,
        ],
    ],

    // Website
    [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => 'MediaTools',
        'url'      => $appUrl,
        'inLanguage' => 'id-ID',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $appUrl . '/?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ],

    // Breadcrumb
    [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'MediaTools',
                'item' => $appUrl,
            ],
        ],
    ],

    // ItemList (Google Rich Result)
    [
        '@context' => 'https://schema.org',
        '@type'    => 'ItemList',
        'name'     => 'Tools Digital Gratis MediaTools',
        'numberOfItems' => count($itemList),
        'itemListElement' => $itemList,
    ],
];

@endphp

{{-- JSON-LD (Single Script, lebih optimal) --}}
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Open Graph --}}
<meta property="og:title" content="MediaTools — Tools Digital Gratis: Invoice, PDF, Background Remover & 10+ Tools">
<meta property="og:description" content="Platform tools produktivitas digital gratis. Background remover, PDF tools, invoice, QR code & lainnya.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $appUrl }}">
<meta property="og:image" content="{{ asset('images/og/home.png') }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="MediaTools — 10+ Tools Digital Gratis">
<meta name="twitter:description" content="Background remover, PDF tools, invoice, QR code & lainnya. Gratis!">
<meta name="twitter:image" content="{{ asset('images/og/home.png') }}">

{{-- Canonical --}}
<link rel="canonical" href="{{ $appUrl }}">
<link rel="alternate" hreflang="id" href="{{ $appUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $appUrl }}">

@endpush