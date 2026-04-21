{{--
    resources/views/seo/pasfoto.blade.php
    Include this partial inside @push('styles') or in the head of the tool view.
    Usage: @include('seo.pasfoto')
--}}
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="keywords"    content="{{ $seo['keywords'] }}">
<link rel="canonical"    href="{{ $seo['canonical'] }}">

{{-- Robots --}}
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

{{-- Open Graph --}}
<meta property="og:title"       content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:type"        content="website">
<meta property="og:url"         content="{{ $seo['canonical'] }}">
<meta property="og:image"       content="{{ $seo['og_image'] }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height"content="630">
<meta property="og:locale"      content="id_ID">
<meta property="og:site_name"   content="MediaTools">

{{-- Twitter Card --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image"       content="{{ $seo['og_image'] }}">

{{-- Structured Data: WebApplication --}}
<script type="application/ld+json">
{!! json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

{{-- Structured Data: FAQPage (boosts rich results) --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Berapa ukuran pas foto 2x3, 3x4, dan 4x6?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Pas foto 2x3 berukuran 2×3 cm, pas foto 3x4 berukuran 3×4 cm, dan pas foto 4x6 berukuran 4×6 cm. Ukuran-ukuran ini adalah standar dokumen resmi Indonesia."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah foto saya dikirim ke server?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Tidak. Semua proses dilakukan sepenuhnya di browser Anda menggunakan JavaScript. Foto Anda tidak pernah diunggah ke server kami, sehingga privasi Anda sepenuhnya terjaga."
      }
    },
    {
      "@type": "Question",
      "name": "Background apa yang digunakan untuk pas foto CPNS?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Untuk pas foto CPNS (Calon Pegawai Negeri Sipil), background yang umum digunakan adalah merah (untuk golongan tertentu) atau biru. Selalu cek persyaratan instansi yang Anda tuju, karena aturan bisa berbeda-beda."
      }
    },
    {
      "@type": "Question",
      "name": "Berapa ukuran file pas foto untuk upload dokumen online?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Sebagian besar portal pemerintah dan swasta membatasi ukuran file foto antara 100KB hingga 300KB. Gunakan fitur kompres di PasFotoOnline untuk mengatur ukuran file sesuai kebutuhan."
      }
    }
  ]
}
</script>
