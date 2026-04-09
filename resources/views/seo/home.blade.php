@push('seo')
{{-- ============================================================
     MEDIATOOLS — SEO Blade: Homepage
     resources/views/seo/home.blade.php

     Covers all 10 tools dengan rich snippets, ItemList,
     FAQPage, dan Organization untuk dominasi SERP.
     ============================================================ --}}
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');

    /*
    |-------------------------------------------------------------
    | Master tool data — single source of truth
    | Dipakai untuk: ItemList, OfferCatalog, SiteLinksSearchBox
    |-------------------------------------------------------------
    */
    $tools = [
        [
            'name'     => 'Invoice Generator Gratis Online',
            'slug'     => 'invoice',
            'desc'     => 'Buat invoice atau tagihan profesional format PDF secara gratis. Kustomisasi logo, item, pajak, dan diskon. Tanpa daftar akun.',
            'keywords' => 'invoice generator gratis, buat invoice online, tagihan pdf, faktur online gratis, invoice otomatis',
            'category' => 'BusinessApplication',
        ],
        [
            'name'     => 'PDF Utilities — Merge Split Compress PDF Gratis',
            'slug'     => 'pdfutilities',
            'desc'     => 'Gabung, pisah, compress, rotate, dan edit PDF secara gratis tanpa instalasi. Proses langsung di browser.',
            'keywords' => 'compress pdf gratis, gabung pdf online, split pdf, merge pdf, pdf tools gratis indonesia',
            'category' => 'BusinessApplication',
        ],
        [
            'name'     => 'Background Remover — Hapus Background Foto Online Gratis',
            'slug'     => 'bg',
            'desc'     => 'Hapus background foto secara otomatis dengan teknologi AI. Hasil transparan PNG bersih dalam 1 klik. Gratis tanpa watermark.',
            'keywords' => 'hapus background foto online gratis, background remover, remove background foto, hapus background foto tanpa watermark',
            'category' => 'ImageObject',
        ],
        [
            'name'     => 'Image Converter — Resize Compress Konversi Gambar Online',
            'slug'     => 'imageconverter',
            'desc'     => 'Konversi JPG, PNG, WebP, resize resolusi, dan compress ukuran gambar secara gratis langsung di browser. Tanpa instalasi.',
            'keywords' => 'resize gambar online, compress gambar gratis, konversi jpg ke png, webp converter, image compressor',
            'category' => 'ImageObject',
        ],
        [
            'name'     => 'File Converter — Konversi PDF ke Word Excel JPG Gratis',
            'slug'     => 'file-converter',
            'desc'     => 'Konversi PDF ke Word, Excel, PowerPoint, JPG dan sebaliknya secara gratis. Mudah, cepat, dan aman.',
            'keywords' => 'konversi pdf ke word gratis, pdf to word online, pdf ke excel, word ke pdf, convert file gratis',
            'category' => 'BusinessApplication',
        ],
        [
            'name'     => 'Media Downloader — Download YouTube TikTok Instagram Gratis',
            'slug'     => 'media-downloader',
            'desc'     => 'Download video YouTube, TikTok, Instagram Reels, dan Facebook tanpa watermark. Format MP4 & MP3 gratis.',
            'keywords' => 'download youtube gratis, download tiktok tanpa watermark, download video instagram, youtube downloader, mp4 downloader',
            'category' => 'SoftwareApplication',
        ],
        [
            'name'     => 'LinkTree Builder — Buat Link in Bio Gratis',
            'slug'     => 'linktree',
            'desc'     => 'Buat halaman bio link yang cantik dan profesional untuk Instagram, TikTok, dan semua media sosial. Gratis selamanya.',
            'keywords' => 'linktree gratis, link in bio, buat link bio instagram, link tree alternatif gratis, bio link page',
            'category' => 'SoftwareApplication',
        ],
        [
            'name'     => 'QR Code Generator — Buat QR Code Custom Gratis',
            'slug'     => 'qr',
            'desc'     => 'Buat QR Code custom untuk menu restoran, pembayaran, kontak bisnis, dan URL. Desain branded dengan logo.',
            'keywords' => 'qr code generator gratis, buat qr code, qr code dengan logo, qr code menu restoran, qr code bisnis',
            'category' => 'SoftwareApplication',
        ],
        [
            'name'     => 'Password Generator — Buat Password Kuat & Aman Gratis',
            'slug'     => 'password-generator',
            'desc'     => 'Generate password kuat, unik, dan aman secara instan. Atur panjang, karakter, dan kompleksitas sesuai kebutuhan.',
            'keywords' => 'password generator, buat password kuat, strong password generator, random password, kata sandi aman',
            'category' => 'SoftwareApplication',
        ],
        [
            'name'     => 'Email Signature Generator — Tanda Tangan Email Profesional',
            'slug'     => 'signature',
            'desc'     => 'Buat tanda tangan email profesional untuk Gmail, Outlook, dan Yahoo. Template modern dengan foto, sosial media, dan branding.',
            'keywords' => 'email signature generator, tanda tangan email, gmail signature, email signature gratis, buat signature email',
            'category' => 'SoftwareApplication',
        ],
    ];

    /* Build ItemList & OfferCatalog once */
    $itemList  = [];
    $offerList = [];

    foreach ($tools as $i => $tool) {
        $url = $appUrl . '/' . $tool['slug'];
        $itemList[] = [
            '@type'       => 'ListItem',
            'position'    => $i + 1,
            'name'        => $tool['name'],
            'url'         => $url,
            'description' => $tool['desc'],
        ];
        $offerList[] = [
            '@type' => 'Offer',
            'itemOffered' => [
                '@type'       => 'SoftwareApplication',
                '@id'         => $url . '#tool',
                'name'        => $tool['name'],
                'url'         => $url,
                'description' => $tool['desc'],
                'applicationCategory' => $tool['category'],
                'operatingSystem'     => 'Web Browser',
                'offers'      => ['@type'=>'Offer','price'=>'0','priceCurrency'=>'IDR'],
            ],
        ];
    }

    $schemas = [

        /* Organization */
        [
            '@context'     => 'https://schema.org',
            '@type'        => 'Organization',
            '@id'          => $appUrl . '/#organization',
            'name'         => 'MediaTools',
            'alternateName'=> 'Media Tools Indonesia',
            'url'          => $appUrl,
            'logo'         => [
                '@type'  => 'ImageObject',
                '@id'    => $appUrl . '/#logo',
                'url'    => $appUrl . '/images/icons-mediatools.png',
                'width'  => 512,
                'height' => 512,
            ],
            'description'  => 'Platform tools produktivitas digital 100% gratis untuk UMKM, freelancer, dan kreator Indonesia.',
            'foundingDate' => '2026',
            'areaServed'   => 'ID',
            'inLanguage'   => 'id-ID',
            'contactPoint' => [
                '@type'             => 'ContactPoint',
                'email'             => 'halo@mediatools.cloud',
                'contactType'       => 'customer support',
                'areaServed'        => 'ID',
                'availableLanguage' => 'Indonesian',
            ],
            'sameAs' => ['https://www.instagram.com/mediatools.id'],
            'hasOfferCatalog' => [
                '@type'           => 'OfferCatalog',
                'name'            => '10+ Tools Digital Gratis',
                'itemListElement' => $offerList,
            ],
        ],

        /* WebPage */
        [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            '@id'         => $appUrl . '/#webpage',
            'url'         => $appUrl,
            'name'        => 'MediaTools — Tools Digital Gratis Indonesia',
            'description' => 'Platform 10+ tools produktivitas digital gratis: invoice, QR code, hapus background, konversi PDF, dan lebih. Langsung pakai tanpa instalasi.',
            'inLanguage'  => 'id-ID',
            'isPartOf'    => ['@id' => $appUrl . '/#website'],
            'about'       => ['@id' => $appUrl . '/#organization'],
            'breadcrumb'  => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [['@type'=>'ListItem','position'=>1,'name'=>'MediaTools','item'=>$appUrl]],
            ],
        ],

        /* ItemList — for Google Rich Results */
        [
            '@context'      => 'https://schema.org',
            '@type'         => 'ItemList',
            'name'          => '10+ Tools Digital Gratis — MediaTools',
            'description'   => 'Daftar tools produktivitas digital gratis: invoice, PDF, background remover, QR code, dan lebih.',
            'url'           => $appUrl,
            'numberOfItems' => count($itemList),
            'itemListElement' => $itemList,
        ],

        /* FAQPage — targets featured snippets */
        [
            '@context' => 'https://schema.org',
            '@type'    => 'FAQPage',
            'mainEntity' => [
                [
                    '@type'          => 'Question',
                    'name'           => 'Apakah semua tools di MediaTools benar-benar gratis?',
                    'acceptedAnswer' => ['@type'=>'Answer','text'=>'Ya, semua tools dasar di MediaTools 100% gratis, tanpa perlu kartu kredit atau mendaftar akun. Langsung buka dan pakai.'],
                ],
                [
                    '@type'          => 'Question',
                    'name'           => 'Bagaimana cara hapus background foto secara gratis online?',
                    'acceptedAnswer' => ['@type'=>'Answer','text'=>'Gunakan Background Remover MediaTools di mediatools.cloud/bg. Upload foto, AI kami akan otomatis menghapus background dalam hitungan detik. Gratis tanpa watermark.'],
                ],
                [
                    '@type'          => 'Question',
                    'name'           => 'Bagaimana cara buat invoice atau tagihan online gratis?',
                    'acceptedAnswer' => ['@type'=>'Answer','text'=>'Gunakan Invoice Generator di mediatools.cloud/invoice. Isi detail klien, item, dan pajak, lalu download PDF profesional secara gratis tanpa daftar.'],
                ],
                [
                    '@type'          => 'Question',
                    'name'           => 'Bagaimana cara download video TikTok tanpa watermark?',
                    'acceptedAnswer' => ['@type'=>'Answer','text'=>'Buka Media Downloader di mediatools.cloud/media-downloader, paste link video TikTok, dan download MP4 tanpa watermark secara gratis.'],
                ],
                [
                    '@type'          => 'Question',
                    'name'           => 'Apakah MediaTools bisa digunakan di smartphone?',
                    'acceptedAnswer' => ['@type'=>'Answer','text'=>'Ya, semua tools MediaTools didesain mobile-first dan responsif penuh. Bisa digunakan langsung di browser HP tanpa perlu install aplikasi.'],
                ],
            ],
        ],

        /* SoftwareApplication — represents the platform */
        [
            '@context'            => 'https://schema.org',
            '@type'               => 'SoftwareApplication',
            '@id'                 => $appUrl . '/#app',
            'name'                => 'MediaTools',
            'url'                 => $appUrl,
            'description'         => 'Platform tools produktivitas digital gratis: invoice, QR code, hapus background, konversi PDF, media downloader, dan banyak lagi.',
            'applicationCategory' => 'UtilitiesApplication',
            'operatingSystem'     => 'Web Browser, iOS, Android',
            'inLanguage'          => 'id-ID',
            'offers'              => ['@type'=>'Offer','price'=>'0','priceCurrency'=>'IDR'],
            'aggregateRating'     => [
                '@type'       => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '10000',
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
            'author' => ['@id' => $appUrl . '/#organization'],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Override OG for homepage specifically --}}
<meta property="og:title"       content="MediaTools — 10+ Tools Digital Gratis: Invoice, PDF, QR Code & Background Remover">
<meta property="og:description" content="Invoice generator, hapus background foto, konversi PDF, QR code, download TikTok/YouTube, dan 10+ tools gratis lainnya. Langsung pakai, tanpa daftar.">
<meta property="og:image"       content="{{ asset('images/og/home.png') }}">

<meta name="twitter:title"       content="MediaTools — 10+ Tools Digital Gratis Indonesia">
<meta name="twitter:description" content="Invoice, PDF, hapus background, QR code, media downloader. Semua gratis, langsung pakai.">

<link rel="canonical"   href="{{ rtrim(config('app.url','https://mediatools.cloud'), '/') }}">
<link rel="alternate"   hreflang="id"        href="{{ rtrim(config('app.url','https://mediatools.cloud'), '/') }}">
<link rel="alternate"   hreflang="x-default" href="{{ rtrim(config('app.url','https://mediatools.cloud'), '/') }}">
@endpush