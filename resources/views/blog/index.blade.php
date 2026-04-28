@extends('layouts.app')

@section('title', 'Blog & Tutorial MediaTools — Tips Produktivitas Digital Gratis')
@section('meta_description', 'Tutorial, panduan, dan tips seputar tools digital gratis: cara hapus background foto, buat invoice, download TikTok, compress PDF, dan banyak lagi.')
@section('meta_keywords', 'tutorial tools online gratis, cara hapus background, cara buat invoice, download tiktok gratis, compress pdf gratis')

@push('schema')
@php
$appUrl = rtrim(config('app.url','https://mediatools.cloud'),'/');
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Blog',
    'name'     => 'Blog MediaTools',
    'url'      => $appUrl . '/blog',
    'description' => 'Tutorial dan panduan penggunaan tools digital gratis untuk UMKM, freelancer, dan kreator Indonesia.',
    'publisher' => ['@id' => $appUrl . '/#organization'],
];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<style>
.blog-wrap { max-width: 1100px; margin: 0 auto; padding: 48px 24px 80px; }
.blog-header { text-align: center; margin-bottom: 48px; }
.blog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
.blog-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
}
.blog-card:hover {
    border-color: var(--border-accent);
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.3);
}
.blog-card-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    background: var(--secondary-bg);
}
.blog-card-body { padding: 22px; flex: 1; display: flex; flex-direction: column; }
.blog-card-cat {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 10px;
}
.blog-card-title {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.4;
    color: var(--text-primary);
    margin-bottom: 10px;
}
.blog-card-desc {
    font-size: 0.85rem;
    color: var(--text-dim);
    line-height: 1.65;
    flex: 1;
    margin-bottom: 16px;
}
.blog-card-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 11px;
    color: var(--text-muted);
    border-top: 1px solid var(--border);
    padding-top: 14px;
}
.blog-card-meta span { display: flex; align-items: center; gap: 5px; }
</style>

<div class="blog-wrap">
    <div class="blog-header reveal">
        <div class="section-tag" style="display:inline-flex;margin-bottom:14px;">
            <i class="fa-solid fa-newspaper"></i> Blog & Tutorial
        </div>
        <h1 style="font-size:clamp(1.8rem,3.5vw,2.5rem);font-weight:800;letter-spacing:-0.03em;margin-bottom:12px;">
            Tips & Tutorial <span class="gradient-text">MediaTools</span>
        </h1>
        <p style="color:var(--text-dim);max-width:520px;margin:0 auto;font-size:0.95rem;line-height:1.7;">
            Panduan praktis menggunakan tools digital gratis untuk meningkatkan produktivitas bisnis dan pekerjaan Anda.
        </p>
    </div>

    <div class="blog-grid">
        @foreach($articles as $i => $article)
        <a href="{{ route('blog.show', $article['slug']) }}"
           class="blog-card reveal"
           style="transition-delay: {{ ($i % 3) * 0.08 }}s">
            <img src="{{ asset($article['og_image']) }}"
                 alt="{{ $article['title'] }}"
                 class="blog-card-img"
                 loading="lazy">
            <div class="blog-card-body">
                <div class="blog-card-cat">
                    <i class="fa-solid fa-tag" style="font-size:9px;"></i>
                    {{ $article['category'] }}
                </div>
                <h2 class="blog-card-title">{{ $article['title'] }}</h2>
                <p class="blog-card-desc">{{ $article['description'] }}</p>
                <div class="blog-card-meta">
                    <span><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($article['date'])->translatedFormat('d M Y') }}</span>
                    <span><i class="fa-regular fa-clock"></i> {{ $article['read_time'] }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
