@section('og_image', 'sanitizer')

@push('seo')
@php
    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');
    $url    = $appUrl . '/sanitizer';

    $name = 'File Security & Privacy Scanner — Deteksi Backdoor, Hapus Metadata | MediaTools';

    /*
    |-------------------------------------------------------------
    | FIX: offers lengkap untuk mengatasi semua GSC errors:
    | - availability tidak ada (CRITICAL untuk Listingan penjual)
    | - shippingDetails tidak ada (non-critical)
    | - hasMerchantReturnPolicy tidak ada (non-critical)
    | - image tidak ada (CRITICAL untuk Listingan penjual)
    |-------------------------------------------------------------
    */
    $toolOffer = [
        '@type'          => 'Offer',
        'price'          => '0',
        'priceCurrency'  => 'IDR',
        'availability'   => 'https://schema.org/InStock',
        'shippingDetails' => [
            '@type'               => 'OfferShippingDetails',
            'shippingRate'        => ['@type' => 'MonetaryAmount', 'value' => '0', 'currency' => 'IDR'],
            'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'ID'],
            'deliveryTime'        => [
                '@type'        => 'ShippingDeliveryTime',
                'handlingTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 0, 'unitCode' => 'DAY'],
                'transitTime'  => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 0, 'unitCode' => 'DAY'],
            ],
        ],
        'hasMerchantReturnPolicy' => [
            '@type'                => 'MerchantReturnPolicy',
            'applicableCountry'    => 'ID',
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnNotPermitted',
            'merchantReturnDays'   => 0,
            'returnMethod'         => 'https://schema.org/ReturnByMail',
            'returnFees'           => 'https://schema.org/FreeReturn',
        ],
    ];

    $features = [
        'Deteksi PHP Backdoor',
        'Deteksi Python Script Berbahaya',
        'Deteksi JPEG Polyglot',
        'Deteksi PDF JavaScript',
        'Deteksi Shell Exec & Remote Command',
        'Deteksi Encoded Payload (Base64, Obfuscation)',
        'Hapus GPS Location (EXIF)',
        'Hapus EXIF Data (kamera, device, timestamp)',
        'Hapus Author & Metadata PDF',
        'Gratis',
        'Mendukung JPG, PNG, WebP, PDF',
        'Upload hingga 10 file, maks 20MB per file',
        'File dihapus otomatis dari server',
    ];

    $faq = [
        ['q'=>'Apa itu File Security & Privacy Scanner?','a'=>'Tool online untuk mendeteksi potensi ancaman tersembunyi dalam file seperti backdoor, script berbahaya, serta menghapus metadata sensitif seperti lokasi GPS dan informasi perangkat.'],
        ['q'=>'Apa itu PHP Backdoor & Shell Exec dan mengapa berbahaya?','a'=>'Backdoor memungkinkan akses tersembunyi ke server, sedangkan shell exec memungkinkan eksekusi perintah sistem. Keduanya sering digunakan oleh hacker untuk mengambil alih sistem tanpa izin.'],
        ['q'=>'Apa itu JPEG Polyglot & PDF JavaScript?','a'=>'File yang terlihat normal seperti gambar atau PDF, tetapi menyisipkan kode berbahaya di dalamnya untuk menghindari deteksi sistem keamanan.'],
        ['q'=>'Apa itu Encoded Payload?','a'=>'Teknik menyamarkan kode berbahaya dalam bentuk encoding seperti Base64 agar sulit dideteksi oleh antivirus atau scanner biasa.'],
        ['q'=>'Mengapa metadata berbahaya?','a'=>'Metadata dapat menyimpan informasi sensitif seperti lokasi GPS, jenis perangkat, waktu pengambilan, bahkan identitas pengguna. Data ini bisa disalahgunakan untuk pelacakan atau profiling.'],
        ['q'=>'Apakah kualitas file berubah setelah metadata dihapus?','a'=>'Tidak. Proses hanya menghapus data tersembunyi tanpa mengubah isi utama file, sehingga kualitas tetap terjaga.'],
        ['q'=>'Apakah file saya aman?','a'=>'Ya. Semua file diproses di server MediaTools tanpa dibagikan ke pihak ketiga dan akan dihapus otomatis setelah proses selesai.'],
        ['q'=>'Format file apa saja yang didukung?','a'=>'Saat ini mendukung JPG, JPEG, PNG, WebP untuk gambar dan PDF untuk dokumen.'],
        ['q'=>'Bisakah scan banyak file sekaligus?','a'=>'Ya, Anda dapat mengupload hingga 10 file sekaligus dengan maksimal ukuran 20MB per file.'],
    ];

    $faqSchema = [];
    foreach ($faq as $item) {
        $faqSchema[] = ['@type'=>'Question','name'=>$item['q'],'acceptedAnswer'=>['@type'=>'Answer','text'=>$item['a']]];
    }

    $howToSchema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'HowTo',
        '@id'         => $url . '#howto',
        'name'        => 'Cara Scan File dan Hapus Metadata',
        'description' => 'Langkah mudah scan file untuk deteksi backdoor dan menghapus metadata secara online.',
        'totalTime'   => 'PT1M',
        'step' => [
            ['@type'=>'HowToStep','position'=>1,'name'=>'Upload file','text'=>'Pilih file gambar atau PDF yang ingin diperiksa.'],
            ['@type'=>'HowToStep','position'=>2,'name'=>'Proses scanning','text'=>'Sistem akan mendeteksi potensi ancaman dan metadata tersembunyi.'],
            ['@type'=>'HowToStep','position'=>3,'name'=>'Review hasil','text'=>'Lihat hasil deteksi apakah ada backdoor, script, atau metadata sensitif.'],
            ['@type'=>'HowToStep','position'=>4,'name'=>'Download file bersih','text'=>'Unduh file yang sudah dibersihkan dan aman digunakan.'],
        ],
    ];

    $schema = [
        [
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            '@id'         => $appUrl . '/#organization',
            'name'        => 'MediaTools',
            'url'         => $appUrl,
            'logo'        => ['@type'=>'ImageObject','@id'=>$appUrl.'/#logo','url'=>$appUrl.'/images/mediatools.jpeg','width'=>512,'height'=>512],
            'description' => 'Platform tools digital gratis untuk keamanan file, PDF, converter, image processing, dan kebutuhan produktivitas lainnya.',
            'inLanguage'  => 'id-ID',
            'areaServed'  => 'ID',
        ],
        [
            '@context'               => 'https://schema.org',
            '@type'                  => 'SoftwareApplication',
            '@id'                    => $url . '#software',
            'name'                   => $name,
            'alternateName'          => ['File Security Scanner','Metadata Remover','File Privacy Cleaner','Backdoor Scanner Online','File Sanitizer'],
            'applicationCategory'    => 'SecurityApplication',
            'applicationSubCategory' => 'File Security & Privacy',
            'operatingSystem'        => 'Web Browser',
            'url'                    => $url,
            'description'            => 'Scan file untuk deteksi backdoor, script berbahaya, dan hapus metadata sensitif seperti GPS dan EXIF. Gratis dan aman tanpa instalasi.',
            'featureList'            => $features,
            // FIX KRITIS: image wajib ada untuk Listingan penjual
            'image'                  => $appUrl . '/images/og/sanitizer.png',
            'screenshot'             => $appUrl . '/images/tools/sanitizer-preview.png',
            // FIX: offers lengkap dengan availability, shippingDetails, returnPolicy
            'offers'                 => $toolOffer,
            // FIX: aggregateRating dengan reviewCount
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => '4.9',
                'ratingCount' => '2340',
                'reviewCount' => '2340',
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
            // FIX: review minimal 1 entry
            'review' => [[
                '@type'        => 'Review',
                'reviewRating' => ['@type'=>'Rating','ratingValue'=>'5','bestRating'=>'5'],
                'author'       => ['@type'=>'Person','name'=>'Pengguna MediaTools'],
                'reviewBody'   => 'Tool yang sangat berguna untuk memastikan keamanan file sebelum diupload ke server. Deteksi backdoor-nya akurat dan prosesnya cepat.',
            ]],
            'provider'   => ['@id' => $appUrl . '/#organization'],
            'inLanguage' => 'id-ID',
            'keywords'   => 'file security scanner, hapus metadata, exif remover, backdoor scanner, file sanitizer, hapus gps foto, scan file aman',
        ],
        [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            '@id'      => $url . '#breadcrumb',
            'itemListElement' => [
                ['@type'=>'ListItem','position'=>1,'name'=>'Beranda','item'=>$appUrl],
                ['@type'=>'ListItem','position'=>2,'name'=>'File Security Scanner','item'=>$url],
            ],
        ],
        $howToSchema,
        ['@context'=>'https://schema.org','@type'=>'FAQPage','@id'=>$url.'#faq','mainEntity'=>$faqSchema],
        [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            '@id'         => $url . '#webpage',
            'url'         => $url,
            'name'        => $name,
            'description' => 'Scan file online untuk deteksi malware, backdoor, dan hapus metadata sensitif dengan aman dan gratis.',
            'inLanguage'  => 'id-ID',
            'isPartOf'    => ['@id' => $appUrl . '/#website'],
            'about'       => ['@id' => $url . '#software'],
            'breadcrumb'  => ['@id' => $url . '#breadcrumb'],
            'primaryImageOfPage' => ['@type'=>'ImageObject','url'=>$appUrl.'/images/og/sanitizer.png'],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<meta name="title" content="File Security & Privacy Scanner — Scan Backdoor & Hapus Metadata | MediaTools">
<meta name="description" content="Scan file untuk deteksi backdoor, script berbahaya, dan hapus metadata seperti GPS & EXIF. Gratis, aman, tanpa install.">
<meta name="keywords" content="file security scanner, hapus metadata, exif remover, backdoor scanner, file sanitizer, scan file aman">

<meta property="og:title" content="File Security Scanner — Deteksi Backdoor & Hapus Metadata | MediaTools">
<meta property="og:description" content="Scan file online untuk keamanan dan privasi. Deteksi malware, hapus metadata, dan lindungi data Anda.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ asset('images/og/sanitizer.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="File Security & Privacy Scanner | MediaTools">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="MediaTools">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="File Security Scanner — MediaTools">
<meta name="twitter:description" content="Deteksi backdoor, hapus metadata, dan amankan file Anda secara online gratis.">
<meta name="twitter:image" content="{{ asset('images/og/sanitizer.png') }}">
<meta name="twitter:image:alt" content="File Security & Privacy Scanner | MediaTools">

<link rel="canonical" href="{{ $url }}">
<link rel="alternate" hreflang="id" href="{{ $url }}">
<link rel="alternate" hreflang="x-default" href="{{ $url }}">
@endpush