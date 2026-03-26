<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $profile['name'] }} | MediaTools</title>
    <meta name="description" content="{{ $profile['bio'] ?? $profile['name'].' - Linktree by MediaTools' }}">
    <meta property="og:title" content="{{ $profile['name'] }}">
    <meta property="og:image" content="{{ $profile['avatar'] }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icons-mediatools.png') }}">

    <style>
    /* ── Reset & Base ──────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html { scroll-behavior: smooth; }

    body {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        -webkit-font-smoothing: antialiased;
        min-height: 100vh;
    }

    /* ── Scrollbar ────────────────────────────────── */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-thumb { border-radius: 99px; }

    /* ── Toast ────────────────────────────────────── */
    #lt-view-toast {
        position: fixed; bottom: 28px; left: 50%;
        transform: translateX(-50%) translateY(50px);
        padding: 12px 24px; border-radius: 20px;
        font-size: 13px; font-weight: 800;
        opacity: 0; z-index: 999; pointer-events: none;
        transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
        white-space: nowrap;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    #lt-view-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

    /* ── Fade In animation ────────────────────────── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .anim { animation: fadeUp 0.5s ease both; }

    /* Staggered delays */
    .d1  { animation-delay: 0.05s; }
    .d2  { animation-delay: 0.12s; }
    .d3  { animation-delay: 0.18s; }
    .d4  { animation-delay: 0.24s; }
    .d5  { animation-delay: 0.30s; }
    .d6  { animation-delay: 0.36s; }
    .d7  { animation-delay: 0.42s; }
    .d8  { animation-delay: 0.48s; }
    .d9  { animation-delay: 0.54s; }
    .d10 { animation-delay: 0.60s; }

    /* ── Ping animation for HOT badge ────────────── */
    @keyframes ping {
        75%,100% { transform: scale(1.8); opacity: 0; }
    }

    .ping-dot { animation: ping 1.5s cubic-bezier(0,0,.2,1) infinite; }

    </style>

    {{-- ══════════════════════════════════════════════
         TEMPLATE-SPECIFIC STYLES
    ═══════════════════════════════════════════════ --}}

    @if(($pageTemplate ?? 'dark') === 'light')
    {{-- ── TEMPLATE: TERANG ── --}}
    <style>
    body {
        background: #f8fafc;
        color: #1e293b;
    }

    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; }

    .lt-page { max-width: 480px; margin: 0 auto; padding: 48px 24px 64px; }

    .lt-profile-ring {
        width: 100px; height: 100px;
        border-radius: 28px;
        border: 3px solid #a3e635;
        padding: 3px;
        background: white;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }

    .lt-profile-img {
        width: 100%; height: 100%;
        border-radius: 22px;
        object-fit: cover;
    }

    .lt-verified {
        display: inline-flex; align-items: center; gap: 4px;
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 99px; padding: 3px 10px;
        font-size: 10px; font-weight: 800;
        color: #2563eb; margin-top: 6px;
        text-transform: uppercase; letter-spacing: 0.08em;
    }

    .lt-name {
        font-size: 1.75rem; font-weight: 900;
        color: #0f172a; letter-spacing: -0.03em;
        margin-top: 14px; line-height: 1.1;
    }

    .lt-username {
        color: #a3e635; font-weight: 700; font-size: 13px;
        margin-top: 3px;
    }

    .lt-bio {
        color: #64748b; font-size: 13px; line-height: 1.65;
        margin-top: 10px; max-width: 340px;
    }

    .lt-stats-bar {
        display: flex; gap: 10px;
        margin: 18px 0 28px;
    }

    .lt-stat-pill {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 14px;
        font-size: 11px; font-weight: 700;
        background: white; border: 1px solid #e2e8f0;
        color: #475569; cursor: pointer;
        transition: all 0.25s ease;
        text-decoration: none;
    }

    .lt-stat-pill:hover { border-color: #a3e635; color: #16a34a; }
    .lt-stat-pill i { color: #a3e635; }

    .lt-link-card {
        display: flex; align-items: center;
        background: white; border: 1px solid #e2e8f0;
        border-radius: 18px; padding: 14px 16px;
        margin-bottom: 10px; text-decoration: none;
        color: #0f172a; transition: all 0.25s ease;
    }

    .lt-link-card:hover {
        border-color: #a3e635;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }

    .lt-link-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: #f1f5f9; display: flex; align-items: center;
        justify-content: center; margin-right: 14px;
        font-size: 18px; color: #a3e635; flex-shrink: 0;
        transition: background 0.25s;
    }

    .lt-link-card:hover .lt-link-icon { background: rgba(163,230,53,0.15); }

    .lt-link-title { font-size: 14px; font-weight: 700; }
    .lt-link-sub   { font-size: 11px; color: #94a3b8; margin-top: 2px; }
    .lt-link-arrow { margin-left: auto; color: #cbd5e1; font-size: 11px; flex-shrink: 0; transition: color .25s; }
    .lt-link-card:hover .lt-link-arrow { color: #a3e635; }

    .lt-hot-badge {
        margin-left: 8px; background: rgba(163,230,53,0.1);
        color: #16a34a; border: 1px solid rgba(163,230,53,0.25);
        font-size: 9px; font-weight: 900; padding: 3px 8px;
        border-radius: 6px; text-transform: uppercase; letter-spacing: 0.08em;
        display: flex; align-items: center; gap: 4px; white-space: nowrap;
    }

    .lt-qr-wrap {
        text-align: center; margin: 32px 0 16px;
        padding: 24px; background: white;
        border: 1px solid #e2e8f0; border-radius: 24px;
        cursor: pointer; transition: all .3s;
    }

    .lt-qr-wrap:hover {
        border-color: #a3e635;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }

    .lt-qr-img { width: 120px; height: 120px; border-radius: 12px; }

    .lt-footer {
        text-align: center; margin-top: 40px;
        padding-top: 24px; border-top: 1px solid #f1f5f9;
    }

    .lt-footer-txt { font-size: 10px; color: #94a3b8; letter-spacing: 0.14em; text-transform: uppercase; }
    .lt-footer-brand { color: #a3e635; font-weight: 900; }

    #lt-view-toast { background: #1e293b; color: white; box-shadow: 0 16px 40px rgba(0,0,0,0.2); }
    </style>

    @elseif(($pageTemplate ?? 'dark') === 'neon')
    {{-- ── TEMPLATE: NEON ── --}}
    <style>
    body {
        background: #0d0821;
        background-image:
            radial-gradient(ellipse at 20% 20%, rgba(139,92,246,0.15) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 80%, rgba(59,130,246,0.12) 0%, transparent 50%);
        color: #e2e8f0;
    }

    ::-webkit-scrollbar-track { background: #0d0821; }
    ::-webkit-scrollbar-thumb { background: rgba(139,92,246,0.4); }

    .lt-page { max-width: 480px; margin: 0 auto; padding: 48px 24px 64px; }

    .lt-profile-ring {
        width: 100px; height: 100px; border-radius: 50%;
        border: 3px solid #8b5cf6;
        padding: 3px;
        box-shadow: 0 0 20px rgba(139,92,246,0.5), 0 0 60px rgba(139,92,246,0.2);
    }

    .lt-profile-img {
        width: 100%; height: 100%;
        border-radius: 50%; object-fit: cover;
    }

    .lt-verified {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(139,92,246,0.15); border: 1px solid rgba(139,92,246,0.3);
        border-radius: 99px; padding: 3px 10px;
        font-size: 10px; font-weight: 800;
        color: #c4b5fd; margin-top: 6px;
        text-transform: uppercase; letter-spacing: 0.08em;
    }

    .lt-name {
        font-size: 1.75rem; font-weight: 900;
        color: #ffffff; letter-spacing: -0.03em;
        margin-top: 14px; line-height: 1.1;
        text-shadow: 0 0 30px rgba(139,92,246,0.4);
    }

    .lt-username {
        color: #a78bfa; font-weight: 700; font-size: 13px; margin-top: 3px;
    }

    .lt-bio {
        color: rgba(226,232,240,0.55); font-size: 13px;
        line-height: 1.65; margin-top: 10px; max-width: 340px;
    }

    .lt-stats-bar { display: flex; gap: 10px; margin: 18px 0 28px; }

    .lt-stat-pill {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 14px;
        font-size: 11px; font-weight: 700;
        background: rgba(139,92,246,0.08);
        border: 1px solid rgba(139,92,246,0.2);
        color: rgba(226,232,240,0.6); cursor: pointer;
        transition: all 0.25s ease; text-decoration: none;
    }

    .lt-stat-pill:hover { border-color: #8b5cf6; color: #c4b5fd; }
    .lt-stat-pill i { color: #8b5cf6; }

    .lt-link-card {
        display: flex; align-items: center;
        background: rgba(139,92,246,0.05);
        border: 1px solid rgba(139,92,246,0.15);
        border-radius: 18px; padding: 14px 16px;
        margin-bottom: 10px; text-decoration: none;
        color: #e2e8f0; transition: all 0.3s ease;
    }

    .lt-link-card:hover {
        background: rgba(139,92,246,0.12);
        border-color: rgba(139,92,246,0.45);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(139,92,246,0.2);
    }

    .lt-link-icon {
        width: 44px; height: 44px; border-radius: 14px;
        background: rgba(139,92,246,0.12);
        display: flex; align-items: center; justify-content: center;
        margin-right: 14px; font-size: 18px; color: #a78bfa; flex-shrink: 0;
        transition: background .25s;
        box-shadow: 0 0 12px rgba(139,92,246,0.2);
    }

    .lt-link-card:hover .lt-link-icon {
        background: rgba(139,92,246,0.25);
        box-shadow: 0 0 20px rgba(139,92,246,0.35);
    }

    .lt-link-title { font-size: 14px; font-weight: 700; color: #f1f5f9; }
    .lt-link-sub   { font-size: 11px; color: rgba(226,232,240,0.4); margin-top: 2px; }
    .lt-link-arrow { margin-left: auto; color: rgba(255,255,255,0.15); font-size: 11px; flex-shrink: 0; transition: color .25s; }
    .lt-link-card:hover .lt-link-arrow { color: #a78bfa; }

    .lt-hot-badge {
        margin-left: 8px; background: rgba(139,92,246,0.15);
        color: #c4b5fd; border: 1px solid rgba(139,92,246,0.3);
        font-size: 9px; font-weight: 900; padding: 3px 8px;
        border-radius: 6px; text-transform: uppercase; letter-spacing: 0.08em;
        display: flex; align-items: center; gap: 4px; white-space: nowrap;
    }

    .lt-qr-wrap {
        text-align: center; margin: 32px 0 16px;
        padding: 24px;
        background: rgba(139,92,246,0.06);
        border: 1px dashed rgba(139,92,246,0.25);
        border-radius: 24px; cursor: pointer; transition: all .3s;
    }

    .lt-qr-wrap:hover {
        border-color: rgba(139,92,246,0.5);
        box-shadow: 0 8px 32px rgba(139,92,246,0.15);
    }

    .lt-qr-img {
        width: 120px; height: 120px; border-radius: 12px;
        filter: invert(1);
    }

    .lt-footer {
        text-align: center; margin-top: 40px;
        padding-top: 24px; border-top: 1px solid rgba(139,92,246,0.1);
    }

    .lt-footer-txt { font-size: 10px; color: rgba(255,255,255,0.2); letter-spacing: 0.14em; text-transform: uppercase; }
    .lt-footer-brand { color: #a78bfa; font-weight: 900; }

    #lt-view-toast { background: #7c3aed; color: white; box-shadow: 0 16px 40px rgba(124,58,237,0.4); }
    </style>

    @else
    {{-- ── TEMPLATE: DARK (default) ── --}}
    <style>
    body {
        background: #040f0f;
        background-image:
            radial-gradient(circle at 15% 15%, rgba(163,230,53,0.06) 0%, transparent 35%),
            radial-gradient(circle at 85% 85%, rgba(163,230,53,0.04) 0%, transparent 35%);
        color: #f0fdf4;
    }

    ::-webkit-scrollbar-track { background: #040f0f; }
    ::-webkit-scrollbar-thumb { background: #1e3a3a; }
    ::-webkit-scrollbar-thumb:hover { background: #a3e635; }

    .lt-page { max-width: 480px; margin: 0 auto; padding: 48px 24px 64px; }

    /* Grid overlay */
    .lt-page::before {
        content: '';
        position: fixed; inset: 0; z-index: -1;
        background-image:
            linear-gradient(rgba(163,230,53,0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(163,230,53,0.025) 1px, transparent 1px);
        background-size: 48px 48px;
        pointer-events: none;
    }

    .lt-profile-ring {
        width: 100px; height: 100px; border-radius: 28px;
        border: 2px solid rgba(163,230,53,0.4);
        padding: 3px;
        background: rgba(11,35,35,0.8);
        box-shadow: 0 0 24px rgba(163,230,53,0.15), 0 8px 32px rgba(0,0,0,0.5);
    }

    .lt-profile-img {
        width: 100%; height: 100%;
        border-radius: 22px; object-fit: cover;
    }

    .lt-verified {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(163,230,53,0.1); border: 1px solid rgba(163,230,53,0.25);
        border-radius: 99px; padding: 3px 10px;
        font-size: 10px; font-weight: 800;
        color: #a3e635; margin-top: 6px;
        text-transform: uppercase; letter-spacing: 0.08em;
    }

    .lt-name {
        font-size: 1.75rem; font-weight: 900;
        color: #ffffff; letter-spacing: -0.03em;
        margin-top: 14px; line-height: 1.1;
    }

    .lt-username { color: #a3e635; font-weight: 700; font-size: 13px; margin-top: 3px; }

    .lt-bio {
        color: rgba(240,253,244,0.45); font-size: 13px;
        line-height: 1.65; margin-top: 10px; max-width: 340px;
    }

    .lt-stats-bar { display: flex; gap: 10px; margin: 18px 0 28px; }

    .lt-stat-pill {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 14px;
        font-size: 11px; font-weight: 700;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        color: rgba(240,253,244,0.5); cursor: pointer;
        transition: all 0.25s ease; text-decoration: none;
    }

    .lt-stat-pill:hover {
        border-color: rgba(163,230,53,0.3);
        color: #a3e635; background: rgba(163,230,53,0.06);
    }

    .lt-stat-pill i { color: #a3e635; }

    .lt-link-card {
        display: flex; align-items: center;
        background: rgba(11,35,35,0.5);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 18px; padding: 14px 16px;
        margin-bottom: 10px; text-decoration: none;
        color: #f0fdf4; transition: all 0.3s ease;
        backdrop-filter: blur(8px);
    }

    .lt-link-card:hover {
        background: rgba(11,35,35,0.7);
        border-color: rgba(163,230,53,0.3);
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(0,0,0,0.4), 0 0 0 1px rgba(163,230,53,0.1);
    }

    .lt-link-icon {
        width: 44px; height: 44px; border-radius: 14px;
        background: rgba(255,255,255,0.05);
        display: flex; align-items: center; justify-content: center;
        margin-right: 14px; font-size: 18px; color: #a3e635; flex-shrink: 0;
        transition: background .25s;
    }

    .lt-link-card:hover .lt-link-icon { background: rgba(163,230,53,0.15); }

    .lt-link-title { font-size: 14px; font-weight: 700; color: #f0fdf4; }
    .lt-link-sub   { font-size: 11px; color: rgba(240,253,244,0.35); margin-top: 2px; }
    .lt-link-arrow { margin-left: auto; color: rgba(255,255,255,0.15); font-size: 11px; flex-shrink: 0; transition: color .25s; }
    .lt-link-card:hover .lt-link-arrow { color: #a3e635; }

    .lt-hot-badge {
        margin-left: 8px; background: rgba(163,230,53,0.1);
        color: #a3e635; border: 1px solid rgba(163,230,53,0.2);
        font-size: 9px; font-weight: 900; padding: 3px 8px;
        border-radius: 6px; text-transform: uppercase; letter-spacing: 0.08em;
        display: flex; align-items: center; gap: 4px; white-space: nowrap;
    }

    .lt-qr-wrap {
        text-align: center; margin: 32px 0 16px;
        padding: 24px;
        background: rgba(163,230,53,0.03);
        border: 1px dashed rgba(163,230,53,0.2);
        border-radius: 24px; cursor: pointer; transition: all .3s;
    }

    .lt-qr-wrap:hover { border-color: rgba(163,230,53,0.45); box-shadow: 0 8px 32px rgba(163,230,53,0.08); }

    .lt-qr-img { width: 120px; height: 120px; border-radius: 12px; background: white; padding: 4px; }

    .lt-footer {
        text-align: center; margin-top: 40px;
        padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.05);
    }

    .lt-footer-txt { font-size: 10px; color: rgba(255,255,255,0.2); letter-spacing: 0.14em; text-transform: uppercase; }
    .lt-footer-brand { color: #a3e635; font-weight: 900; }

    #lt-view-toast { background: #a3e635; color: #040f0f; box-shadow: 0 16px 40px rgba(163,230,53,0.3); }
    </style>
    @endif
</head>

<body>
<div class="lt-page">

    {{-- ── Profile ── --}}
    <div class="anim d1" style="text-align:center;">
        <div style="display:inline-block;position:relative;">
            <div class="lt-profile-ring">
                <img src="{{ $profile['avatar'] }}"
                     alt="{{ $profile['name'] }}"
                     class="lt-profile-img"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($profile['name']) }}&size=100'">
            </div>
            {{-- Verified dot --}}
            @if($profile['verified'])
            <div style="position:absolute;bottom:0;right:-2px;width:26px;height:26px;background:white;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid {{ ($pageTemplate??'dark')==='dark' ? '#040f0f' : (($pageTemplate??'dark')==='neon' ? '#0d0821' : '#f8fafc') }};">
                <i class="fa-solid fa-circle-check" style="color:#2563eb;font-size:14px;"></i>
            </div>
            @endif
        </div>

        @if($profile['verified'])
        <div class="lt-verified anim d2">
            <i class="fa-solid fa-shield-check" style="font-size:9px;"></i>
            Verified Creator
        </div>
        @endif

        <h1 class="lt-name anim d2">{{ $profile['name'] }}</h1>
        <p class="lt-username anim d3">{{ $profile['username'] }}</p>

        @if($profile['bio'])
        <p class="lt-bio anim d3">{{ $profile['bio'] }}</p>
        @endif
    </div>

    {{-- ── Stats Bar ── --}}
    <div class="lt-stats-bar anim d4" style="justify-content:center;">
        <div class="lt-stat-pill">
            <i class="fa-solid fa-eye" style="font-size:10px;"></i>
            {{ $profile['visitors'] }} Views
        </div>
        <button onclick="sharePage()" class="lt-stat-pill" style="border:none;cursor:pointer;font-family:inherit;">
            <i class="fa-solid fa-share-nodes" style="font-size:10px;"></i>
            Bagikan
        </button>
    </div>

    {{-- ── Links ── --}}
    @php $delay = 5; @endphp
    @foreach($links as $link)
    <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="lt-link-card anim d{{ min($delay++, 10) }}">
        <div class="lt-link-icon">
            <i class="fa-solid {{ $link['icon'] ?? 'fa-link' }}"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div class="lt-link-title">{{ $link['title'] }}</div>
            <div class="lt-link-sub">{{ $link['subtitle'] ?? 'Kunjungi tautan ini' }}</div>
        </div>
        @if(!empty($link['is_priority']))
        <div class="lt-hot-badge">
            <span style="position:relative;display:inline-flex;">
                <span class="ping-dot" style="position:absolute;width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.6;top:1px;left:1px;"></span>
                <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:currentColor;"></span>
            </span>
            Hot
        </div>
        @endif
        <i class="fa-solid fa-chevron-right lt-link-arrow" style="margin-left:10px;"></i>
    </a>
    @endforeach

    {{-- ── Socials ── --}}
    @foreach($socials as $social)
    <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="lt-link-card anim d{{ min($delay++, 10) }}">
        <div class="lt-link-icon">
            <i class="fa-brands {{ $social['icon'] }}"></i>
        </div>
        <div style="flex:1;">
            <div class="lt-link-title">
                @php
                    $names = [
                        'fa-instagram' => 'Instagram',
                        'fa-tiktok'    => 'TikTok',
                        'fa-whatsapp'  => 'WhatsApp',
                        'fa-x-twitter' => 'Twitter / X',
                        'fa-youtube'   => 'YouTube',
                        'fa-linkedin'  => 'LinkedIn',
                        'fa-facebook'  => 'Facebook',
                    ];
                @endphp
                {{ $names[$social['icon']] ?? 'Social Media' }}
            </div>
            <div class="lt-link-sub">Ikuti aktivitas kami</div>
        </div>
        <i class="fa-solid fa-chevron-right lt-link-arrow"></i>
    </a>
    @endforeach

    {{-- ── QR Code ── --}}
    <div class="lt-qr-wrap anim d10" onclick="downloadQR()" title="Klik untuk unduh QR">
        <div style="display:inline-block;background:white;padding:10px;border-radius:14px;margin-bottom:10px;position:relative;">
            <img id="qrImage"
                 src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode(url()->current()) }}&color=040f0f&bgcolor=ffffff&margin=5"
                 alt="QR Code"
                 class="lt-qr-img">
        </div>
        <p style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.16em;opacity:.6;margin-bottom:3px;">
            Scan to Share
        </p>
        <p style="font-size:9px;opacity:.35;text-transform:uppercase;letter-spacing:.1em;">
            Klik untuk unduh PNG
        </p>
    </div>

    {{-- ── Footer ── --}}
    <div class="lt-footer anim d10">
        <p class="lt-footer-txt">
            Powered by <span class="lt-footer-brand">MEDIATOOLS</span>
        </p>
    </div>

</div>

{{-- Toast --}}
<div id="lt-view-toast"></div>

<script>
function sharePage() {
    const url = window.location.href;

    if (navigator.share) {
        navigator.share({ title: '{{ addslashes($profile['name']) }}', url })
            .catch(() => fallbackCopy(url));
    } else {
        fallbackCopy(url);
    }
}

function fallbackCopy(url) {
    navigator.clipboard?.writeText(url).then(() => {
        showToast('Link berhasil disalin! 🚀');
    }).catch(() => {
        const ta = document.createElement('textarea');
        ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast('Link berhasil disalin! 🚀');
    });
}

async function downloadQR() {
    const img  = document.getElementById('qrImage');
    const slug = '{{ Str::slug($profile['name']) }}';

    try {
        const res  = await fetch(img.src);
        const blob = await res.blob();
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url; a.download = `QR-${slug}.png`;
        document.body.appendChild(a); a.click();
        document.body.removeChild(a); URL.revokeObjectURL(url);
        showToast('QR Code diunduh!');
    } catch {
        showToast('Gagal mengunduh QR.', true);
    }
}

let toastTimer;
function showToast(msg, isError = false) {
    const t = document.getElementById('lt-view-toast');
    t.textContent = msg;
    if (isError) { t.style.background = '#ef4444'; t.style.color = '#fff'; }
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
</body>
</html>