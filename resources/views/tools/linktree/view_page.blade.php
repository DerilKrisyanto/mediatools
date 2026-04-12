<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $profile['name'] }} | MediaTools</title>
    <meta name="description" content="{{ $profile['bio'] ?? $profile['name'].' - Linktree by MediaTools' }}">
    <meta property="og:title"       content="{{ $profile['name'] }}">
    <meta property="og:image"       content="{{ $profile['avatar'] }}">
    <meta property="og:description" content="{{ $profile['bio'] ?? '' }}">
    <meta property="og:type"        content="profile">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/mediatools.jpeg') }}">

    <style>
    /* ══ Reset ══════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        -webkit-font-smoothing: antialiased;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { border-radius: 99px; }

    /* ══ Animations ════════════════════════════════════ */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(24px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.85); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes pulse-ring {
        0%   { transform: scale(1);    opacity: 0.6; }
        100% { transform: scale(1.35); opacity: 0; }
    }
    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-6px); }
    }
    @keyframes ping {
        75%, 100% { transform: scale(1.8); opacity: 0; }
    }

    .anim     { animation: fadeUp  0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .anim-pop { animation: scaleIn 0.5s  cubic-bezier(0.34,1.56,0.64,1) both; }
    .ping-dot { animation: ping 1.5s cubic-bezier(0,0,.2,1) infinite; }

    .d1  { animation-delay: 0.04s; }
    .d2  { animation-delay: 0.10s; }
    .d3  { animation-delay: 0.16s; }
    .d4  { animation-delay: 0.22s; }
    .d5  { animation-delay: 0.28s; }
    .d6  { animation-delay: 0.34s; }
    .d7  { animation-delay: 0.40s; }
    .d8  { animation-delay: 0.46s; }
    .d9  { animation-delay: 0.52s; }
    .d10 { animation-delay: 0.58s; }

    /* ══ Toast ═════════════════════════════════════════ */
    #lt-view-toast {
        position: fixed; bottom: 28px; left: 50%;
        transform: translateX(-50%) translateY(50px);
        padding: 12px 24px; border-radius: 20px;
        font-size: 13px; font-weight: 800;
        opacity: 0; z-index: 9999; pointer-events: none;
        transition: all 0.4s cubic-bezier(0.23,1,0.32,1);
        white-space: nowrap;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    #lt-view-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

    /* ══ Layout ════════════════════════════════════════ */
    .lt-page {
        width: 100%;
        max-width: 460px;
        padding: 52px 20px 72px;
        margin: 0 auto;
    }

    /* ══ Profile Header ════════════════════════════════ */
    .lt-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    /* Avatar with animated ring */
    .lt-avatar-wrap {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .lt-profile-ring {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lt-profile-img {
        width: 100%; height: 100%;
        object-fit: cover;
    }

    /* Verified badge */
    .lt-verified-dot {
        position: absolute;
        bottom: -2px; right: -2px;
        width: 28px; height: 28px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        border: 2.5px solid;
        z-index: 2;
    }

    .lt-verified-badge {
        display: inline-flex; align-items: center; gap: 5px;
        border-radius: 99px; padding: 4px 12px;
        font-size: 10px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.1em;
        margin-bottom: 10px;
    }

    /* Name & username */
    .lt-name {
        font-family: 'Outfit', sans-serif;
        font-size: 2rem; font-weight: 900;
        letter-spacing: -0.04em;
        line-height: 1.05;
        margin-bottom: 5px;
    }

    .lt-username {
        font-size: 13px; font-weight: 600;
        letter-spacing: 0.01em;
        margin-bottom: 14px;
    }

    /* ══ Bio — always centered, clean, readable ════════ */
    .lt-bio {
        font-size: 13.5px;
        font-weight: 400;
        line-height: 1.7;
        text-align: center;
        max-width: 320px;
        margin: 0 auto 22px;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    /* ══ Stats Bar ══════════════════════════════════════ */
    .lt-stats-bar {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-bottom: 32px;
    }

    .lt-stat-pill {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 18px; border-radius: 99px;
        font-size: 11.5px; font-weight: 700;
        cursor: pointer; transition: all 0.25s ease;
        text-decoration: none;
        font-family: 'Plus Jakarta Sans', sans-serif;
        border: none;
    }

    /* ══ Links Section ══════════════════════════════════ */
    .lt-links-section { width: 100%; }

    .lt-section-label {
        font-size: 9.5px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.15em;
        margin-bottom: 12px;
        padding-left: 4px;
    }

    .lt-link-card {
        display: flex; align-items: center;
        border-radius: 20px; padding: 15px 16px 15px 15px;
        margin-bottom: 10px; text-decoration: none;
        transition: all 0.28s cubic-bezier(0.22,1,0.36,1);
        position: relative; overflow: hidden;
    }

    /* Shimmer effect on hover */
    .lt-link-card::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.04) 50%, transparent 60%);
        background-size: 400px 100%;
        background-position: -400px 0;
        transition: background-position 0.5s ease;
    }
    .lt-link-card:hover::before {
        background-position: 400px 0;
    }

    .lt-link-icon {
        width: 46px; height: 46px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        margin-right: 14px; font-size: 19px; flex-shrink: 0;
        transition: transform .25s cubic-bezier(0.34,1.56,0.64,1);
    }
    .lt-link-card:hover .lt-link-icon { transform: scale(1.12) rotate(-4deg); }

    .lt-link-text { flex: 1; min-width: 0; }
    .lt-link-title { font-size: 14px; font-weight: 700; letter-spacing: -0.01em; }
    .lt-link-sub   { font-size: 11px; margin-top: 2px; }

    .lt-link-arrow {
        margin-left: 10px; font-size: 11px; flex-shrink: 0;
        transition: transform .25s ease, opacity .25s ease;
        opacity: 0.3;
    }
    .lt-link-card:hover .lt-link-arrow { transform: translateX(3px); opacity: 1; }

    /* HOT badge */
    .lt-hot-badge {
        margin-left: 8px; font-size: 9px; font-weight: 900; padding: 3px 8px;
        border-radius: 6px; text-transform: uppercase; letter-spacing: 0.08em;
        display: flex; align-items: center; gap: 4px; white-space: nowrap; flex-shrink: 0;
    }

    /* ══ Socials divider ════════════════════════════════ */
    .lt-divider {
        display: flex; align-items: center; gap: 12px;
        margin: 24px 0 16px;
    }
    .lt-divider-line { flex: 1; height: 1px; }
    .lt-divider-txt  { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }

    /* ══ QR Code ════════════════════════════════════════ */
    .lt-qr-wrap {
        display: flex; flex-direction: column; align-items: center;
        margin: 28px 0 16px;
        padding: 28px 24px 22px;
        border-radius: 24px;
        cursor: pointer; transition: all .3s ease;
        position: relative; overflow: hidden;
    }

    .lt-qr-img-frame {
        background: white;
        padding: 10px; border-radius: 16px;
        margin-bottom: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        animation: float 4s ease-in-out infinite;
        display: inline-block;
    }

    .lt-qr-img { width: 118px; height: 118px; border-radius: 8px; display: block; }

    .lt-qr-label {
        font-size: 10px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.16em;
        margin-bottom: 4px;
    }
    .lt-qr-hint { font-size: 9.5px; opacity: 0.4; text-transform: uppercase; letter-spacing: 0.1em; }

    /* ══ Footer ══════════════════════════════════════════ */
    .lt-footer {
        text-align: center; margin-top: 36px;
        padding-top: 24px;
    }
    .lt-footer-txt   { font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; }
    .lt-footer-brand { font-weight: 900; }

    @media (max-width: 480px) {
        .lt-page { padding: 40px 18px 60px; }
        .lt-name { font-size: 1.75rem; }
    }
    </style>

    {{-- ══ TEMPLATE STYLES ══════════════════════════════ --}}

    @if(($pageTemplate ?? 'dark') === 'light')
    {{-- ╔═════════════════════════════╗
         ║   TEMPLATE: LIGHT / CLEAN  ║
         ╚═════════════════════════════╝ --}}
    <style>
    body {
        background: #f7f9fc;
        color: #1a2332;
    }
    body::before {
        content: '';
        position: fixed; inset: 0; z-index: -1; pointer-events: none;
        background:
            radial-gradient(ellipse 900px 600px at 50% -80px, rgba(163,230,53,0.12) 0%, transparent 70%),
            linear-gradient(180deg, #f7f9fc 0%, #eef2f7 100%);
    }

    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #d1d5db; }

    /* Avatar */
    .lt-profile-ring {
        width: 108px; height: 108px;
        border-radius: 32px;
        padding: 3px;
        background: linear-gradient(135deg, #a3e635 0%, #65a30d 100%);
        box-shadow: 0 12px 36px rgba(101,163,13,0.22), 0 2px 8px rgba(0,0,0,0.08);
    }
    .lt-profile-img { border-radius: 28px; }

    .lt-verified-dot {
        background: white;
        border-color: #f7f9fc;
    }
    .lt-verified-badge {
        background: rgba(163,230,53,0.12);
        border: 1px solid rgba(101,163,13,0.2);
        color: #4d7c0f;
    }

    .lt-name { color: #0f172a; }
    .lt-username { color: #65a30d; }
    .lt-bio { color: #64748b; }

    /* Stats */
    .lt-stat-pill {
        background: white;
        border: 1.5px solid #e2e8f0;
        color: #475569;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .lt-stat-pill:hover { border-color: #a3e635; color: #3d6b04; background: rgba(163,230,53,0.06); }
    .lt-stat-pill i { color: #a3e635; }

    /* Link cards */
    .lt-link-card {
        background: white;
        border: 1.5px solid #e8edf3;
        color: #1a2332;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .lt-link-card:hover {
        border-color: rgba(163,230,53,0.5);
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(101,163,13,0.12), 0 2px 8px rgba(0,0,0,0.04);
    }
    .lt-link-icon {
        background: #f1f5f9;
        color: #65a30d;
    }
    .lt-link-card:hover .lt-link-icon { background: rgba(163,230,53,0.15); }
    .lt-link-title { color: #1a2332; }
    .lt-link-sub   { color: #94a3b8; }
    .lt-link-arrow { color: #cbd5e1; }
    .lt-link-card:hover .lt-link-arrow { color: #65a30d; }

    .lt-hot-badge { background: rgba(163,230,53,0.12); color: #4d7c0f; border: 1px solid rgba(163,230,53,0.25); }

    .lt-section-label { color: #94a3b8; }
    .lt-divider-line   { background: #e8edf3; }
    .lt-divider-txt    { color: #94a3b8; }

    /* QR */
    .lt-qr-wrap {
        background: white;
        border: 1.5px solid #e8edf3;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    }
    .lt-qr-wrap:hover {
        border-color: rgba(163,230,53,0.4);
        box-shadow: 0 12px 32px rgba(101,163,13,0.1);
        transform: translateY(-2px);
    }
    .lt-qr-label  { color: #1a2332; }
    .lt-qr-hint   { color: #94a3b8; }

    .lt-footer { border-top: 1.5px solid #e8edf3; }
    .lt-footer-txt   { color: #94a3b8; }
    .lt-footer-brand { color: #65a30d; }

    #lt-view-toast { background: #1a2332; color: white; box-shadow: 0 16px 40px rgba(0,0,0,0.15); }
    </style>

    @elseif(($pageTemplate ?? 'dark') === 'neon')
    {{-- ╔══════════════════════════╗
         ║   TEMPLATE: NEON / VIBE  ║
         ╚══════════════════════════╝ --}}
    <style>
    body {
        background: #08051a;
        color: #e8e0ff;
    }
    body::before {
        content: '';
        position: fixed; inset: 0; z-index: -1; pointer-events: none;
        background:
            radial-gradient(ellipse 700px 500px at 15% 10%,  rgba(168,85,247,0.18) 0%, transparent 60%),
            radial-gradient(ellipse 600px 400px at 85% 80%,  rgba(59,130,246,0.14) 0%, transparent 60%),
            radial-gradient(ellipse 400px 300px at 50% 50%,  rgba(236,72,153,0.06) 0%, transparent 60%);
    }
    /* Grain texture */
    body::after {
        content: '';
        position: fixed; inset: 0; z-index: -1; pointer-events: none; opacity: 0.025;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
        background-size: 256px 256px;
    }

    ::-webkit-scrollbar-track { background: #08051a; }
    ::-webkit-scrollbar-thumb { background: rgba(168,85,247,0.35); }

    /* Avatar */
    .lt-avatar-wrap::before {
        content: '';
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        background: conic-gradient(from 0deg, #a855f7, #3b82f6, #ec4899, #a855f7);
        animation: pulse-ring 2.5s ease-out infinite;
        opacity: 0.5;
    }
    .lt-profile-ring {
        width: 110px; height: 110px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(135deg, #a855f7, #3b82f6);
        box-shadow: 0 0 32px rgba(168,85,247,0.5), 0 0 80px rgba(168,85,247,0.2);
        position: relative; z-index: 1;
    }
    .lt-profile-img { border-radius: 50%; }

    .lt-verified-dot { background: #08051a; border-color: #08051a; }
    .lt-verified-badge {
        background: rgba(168,85,247,0.15);
        border: 1px solid rgba(168,85,247,0.35);
        color: #d8b4fe;
    }

    .lt-name {
        color: #ffffff;
        text-shadow: 0 0 40px rgba(168,85,247,0.5), 0 0 80px rgba(168,85,247,0.2);
    }
    .lt-username { color: #c084fc; }
    .lt-bio { color: rgba(232,224,255,0.5); }

    /* Stats */
    .lt-stat-pill {
        background: rgba(168,85,247,0.08);
        border: 1px solid rgba(168,85,247,0.2);
        color: rgba(232,224,255,0.6);
    }
    .lt-stat-pill:hover { border-color: #a855f7; color: #d8b4fe; background: rgba(168,85,247,0.15); }
    .lt-stat-pill i { color: #c084fc; }

    /* Link cards */
    .lt-link-card {
        background: rgba(168,85,247,0.05);
        border: 1px solid rgba(168,85,247,0.15);
        color: #e8e0ff;
        backdrop-filter: blur(12px);
    }
    .lt-link-card:hover {
        background: rgba(168,85,247,0.12);
        border-color: rgba(168,85,247,0.5);
        transform: translateY(-3px);
        box-shadow: 0 10px 32px rgba(168,85,247,0.2), 0 0 0 1px rgba(168,85,247,0.15);
    }
    .lt-link-icon {
        background: rgba(168,85,247,0.12);
        color: #c084fc;
        box-shadow: 0 0 16px rgba(168,85,247,0.2);
    }
    .lt-link-card:hover .lt-link-icon {
        background: rgba(168,85,247,0.25);
        box-shadow: 0 0 24px rgba(168,85,247,0.35);
    }
    .lt-link-title { color: #f1ecff; }
    .lt-link-sub   { color: rgba(232,224,255,0.4); }
    .lt-link-arrow { color: rgba(255,255,255,0.18); }
    .lt-link-card:hover .lt-link-arrow { color: #c084fc; }

    .lt-hot-badge { background: rgba(168,85,247,0.15); color: #d8b4fe; border: 1px solid rgba(168,85,247,0.3); }

    .lt-section-label { color: rgba(196,180,254,0.4); }
    .lt-divider-line   { background: rgba(168,85,247,0.12); }
    .lt-divider-txt    { color: rgba(196,180,254,0.35); }

    /* QR */
    .lt-qr-wrap {
        background: rgba(168,85,247,0.05);
        border: 1px dashed rgba(168,85,247,0.3);
        backdrop-filter: blur(8px);
    }
    .lt-qr-wrap:hover {
        border-color: rgba(168,85,247,0.6);
        box-shadow: 0 12px 40px rgba(168,85,247,0.15);
        transform: translateY(-2px);
    }
    .lt-qr-img { filter: none; }
    .lt-qr-label  { color: rgba(232,224,255,0.7); }

    .lt-footer { border-top: 1px solid rgba(168,85,247,0.1); }
    .lt-footer-txt   { color: rgba(255,255,255,0.2); }
    .lt-footer-brand { color: #a855f7; }

    #lt-view-toast { background: linear-gradient(135deg,#7c3aed,#4f46e5); color: white; box-shadow: 0 16px 40px rgba(124,58,237,0.4); }
    </style>

    @else
    {{-- ╔════════════════════════════╗
         ║   TEMPLATE: DARK (default) ║
         ╚════════════════════════════╝ --}}
    <style>
    body {
        background: #040f0f;
        color: #f0fdf4;
    }
    body::before {
        content: '';
        position: fixed; inset: 0; z-index: -1; pointer-events: none;
        background:
            radial-gradient(ellipse 800px 500px at 50% -60px, rgba(163,230,53,0.08) 0%, transparent 65%),
            radial-gradient(ellipse 500px 400px at 0% 100%,   rgba(163,230,53,0.04) 0%, transparent 60%),
            radial-gradient(ellipse 500px 400px at 100% 0%,   rgba(163,230,53,0.03) 0%, transparent 60%);
    }
    /* Subtle grid overlay */
    body::after {
        content: '';
        position: fixed; inset: 0; z-index: -1; pointer-events: none;
        background-image:
            linear-gradient(rgba(163,230,53,0.022) 1px, transparent 1px),
            linear-gradient(90deg, rgba(163,230,53,0.022) 1px, transparent 1px);
        background-size: 52px 52px;
    }

    ::-webkit-scrollbar-track { background: #040f0f; }
    ::-webkit-scrollbar-thumb { background: rgba(163,230,53,0.25); }
    ::-webkit-scrollbar-thumb:hover { background: rgba(163,230,53,0.5); }

    /* Avatar */
    .lt-profile-ring {
        width: 108px; height: 108px;
        border-radius: 30px;
        padding: 3px;
        background: linear-gradient(135deg, rgba(163,230,53,0.9) 0%, rgba(101,163,13,0.6) 100%);
        box-shadow:
            0 0 0 1px rgba(163,230,53,0.2),
            0 0 30px rgba(163,230,53,0.18),
            0 12px 40px rgba(0,0,0,0.5);
    }
    .lt-profile-img { border-radius: 26px; }

    .lt-verified-dot { background: #040f0f; border-color: #040f0f; }
    .lt-verified-badge {
        background: rgba(163,230,53,0.1);
        border: 1px solid rgba(163,230,53,0.25);
        color: #a3e635;
    }

    .lt-name { color: #ffffff; }
    .lt-username { color: #a3e635; }
    .lt-bio { color: rgba(240,253,244,0.48); }

    /* Stats */
    .lt-stat-pill {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        color: rgba(240,253,244,0.5);
    }
    .lt-stat-pill:hover {
        border-color: rgba(163,230,53,0.35);
        color: #a3e635;
        background: rgba(163,230,53,0.07);
    }
    .lt-stat-pill i { color: #a3e635; }

    /* Link cards */
    .lt-link-card {
        background: rgba(11,35,35,0.55);
        border: 1px solid rgba(255,255,255,0.07);
        color: #f0fdf4;
        backdrop-filter: blur(10px);
    }
    .lt-link-card:hover {
        background: rgba(11,35,35,0.75);
        border-color: rgba(163,230,53,0.32);
        transform: translateY(-3px);
        box-shadow: 0 14px 36px rgba(0,0,0,0.45), 0 0 0 1px rgba(163,230,53,0.1);
    }
    .lt-link-icon {
        background: rgba(255,255,255,0.05);
        color: #a3e635;
        border: 1px solid rgba(255,255,255,0.05);
    }
    .lt-link-card:hover .lt-link-icon { background: rgba(163,230,53,0.14); border-color: rgba(163,230,53,0.2); }
    .lt-link-title { color: #f0fdf4; }
    .lt-link-sub   { color: rgba(240,253,244,0.35); }
    .lt-link-arrow { color: rgba(255,255,255,0.16); }
    .lt-link-card:hover .lt-link-arrow { color: #a3e635; }

    .lt-hot-badge {
        background: rgba(163,230,53,0.1);
        color: #a3e635;
        border: 1px solid rgba(163,230,53,0.2);
    }

    .lt-section-label { color: rgba(163,230,53,0.35); }
    .lt-divider-line   { background: rgba(255,255,255,0.06); }
    .lt-divider-txt    { color: rgba(240,253,244,0.2); }

    /* QR */
    .lt-qr-wrap {
        background: rgba(163,230,53,0.03);
        border: 1px dashed rgba(163,230,53,0.22);
    }
    .lt-qr-wrap:hover {
        border-color: rgba(163,230,53,0.45);
        box-shadow: 0 10px 36px rgba(163,230,53,0.08);
        transform: translateY(-2px);
    }
    .lt-qr-label  { color: rgba(240,253,244,0.65); }

    .lt-footer { border-top: 1px solid rgba(255,255,255,0.05); }
    .lt-footer-txt   { color: rgba(255,255,255,0.18); }
    .lt-footer-brand { color: #a3e635; }

    #lt-view-toast { background: #a3e635; color: #040f0f; box-shadow: 0 16px 40px rgba(163,230,53,0.3); }
    </style>
    @endif
</head>

<body>
<div class="lt-page">

    {{-- ══ PROFILE HEADER ══════════════════════════════ --}}
    <div class="lt-header">

        {{-- Avatar --}}
        <div class="lt-avatar-wrap anim-pop d1">
            <div class="lt-profile-ring">
                <img src="{{ $profile['avatar'] }}"
                     alt="{{ $profile['name'] }}"
                     class="lt-profile-img"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($profile['name']) }}&size=200&background=a3e635&color=040f0f&bold=true'">
            </div>
            @if($profile['verified'])
            <div class="lt-verified-dot">
                <i class="fa-solid fa-circle-check" style="color:#2563eb;font-size:14px;"></i>
            </div>
            @endif
        </div>

        @if($profile['verified'])
        <div class="lt-verified-badge anim d2">
            <i class="fa-solid fa-shield-check" style="font-size:9px;"></i>
            Verified Creator
        </div>
        @endif

        {{-- Name --}}
        <h1 class="lt-name anim d2">{{ $profile['name'] }}</h1>

        {{-- Username --}}
        <p class="lt-username anim d3">{{ $profile['username'] }}</p>

        {{-- Bio — always centered, max-width keeps it readable --}}
        @if($profile['bio'])
        <p class="lt-bio anim d3">{{ $profile['bio'] }}</p>
        @endif

        {{-- Stats Bar --}}
        <div class="lt-stats-bar anim d4">
            <div class="lt-stat-pill">
                <i class="fa-solid fa-eye" style="font-size:10px;"></i>
                {{ $profile['visitors'] }} Views
            </div>
            <button onclick="sharePage()"
                    class="lt-stat-pill"
                    style="cursor:pointer;font-family:inherit;">
                <i class="fa-solid fa-share-nodes" style="font-size:10px;"></i>
                Bagikan
            </button>
        </div>

    </div>{{-- /lt-header --}}

    {{-- ══ LINKS ══════════════════════════════════════ --}}
    @if(count($links))
    <div class="lt-links-section">
        <p class="lt-section-label anim d4">
            <i class="fa-solid fa-link" style="font-size:8px;margin-right:5px;"></i>Tautan
        </p>
        @php $delay = 5; @endphp
        @foreach($links as $link)
        <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
           class="lt-link-card anim d{{ min($delay++, 10) }}">
            <div class="lt-link-icon">
                <i class="fa-solid {{ $link['icon'] ?? 'fa-link' }}"></i>
            </div>
            <div class="lt-link-text">
                <div class="lt-link-title">{{ $link['title'] }}</div>
                <div class="lt-link-sub">{{ $link['subtitle'] ?? 'Kunjungi tautan ini' }}</div>
            </div>
            @if(!empty($link['is_priority']))
            <div class="lt-hot-badge">
                <span style="position:relative;display:inline-flex;">
                    <span class="ping-dot" style="position:absolute;width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.6;top:0;left:0;"></span>
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:currentColor;"></span>
                </span>
                Hot
            </div>
            @endif
            <i class="fa-solid fa-arrow-right lt-link-arrow"></i>
        </a>
        @endforeach
    </div>
    @endif

    {{-- ══ SOCIALS ══════════════════════════════════════ --}}
    @if(count($socials))
    @php
        $socialNames = [
            'fa-instagram' => ['Instagram',  'Ikuti di Instagram'],
            'fa-tiktok'    => ['TikTok',     'Saksikan konten kami'],
            'fa-whatsapp'  => ['WhatsApp',   'Chat langsung dengan kami'],
            'fa-x-twitter' => ['Twitter / X','Update terkini di sini'],
            'fa-youtube'   => ['YouTube',    'Tonton video kami'],
            'fa-linkedin'  => ['LinkedIn',   'Koneksi profesional'],
            'fa-facebook'  => ['Facebook',   'Ikuti halaman kami'],
        ];
    @endphp

    @if(count($links))
    <div class="lt-divider anim d{{ min($delay, 10) }}">
        <div class="lt-divider-line"></div>
        <span class="lt-divider-txt">Sosial Media</span>
        <div class="lt-divider-line"></div>
    </div>
    @else
    <p class="lt-section-label anim d4" style="margin-bottom:12px;">
        <i class="fa-solid fa-share-nodes" style="font-size:8px;margin-right:5px;"></i>Sosial Media
    </p>
    @endif

    @foreach($socials as $social)
    @php
        $sInfo  = $socialNames[$social['icon']] ?? ['Social Media', 'Ikuti aktivitas kami'];
        $sName  = $sInfo[0];
        $sSub   = $sInfo[1];
    @endphp
    <a href="{{ $social['url'] }}" target="_blank" rel="noopener"
       class="lt-link-card anim d{{ min($delay++, 10) }}">
        <div class="lt-link-icon">
            <i class="fa-brands {{ $social['icon'] }}"></i>
        </div>
        <div class="lt-link-text">
            <div class="lt-link-title">{{ $sName }}</div>
            <div class="lt-link-sub">{{ $sSub }}</div>
        </div>
        <i class="fa-solid fa-arrow-right lt-link-arrow"></i>
    </a>
    @endforeach
    @endif

    {{-- ══ QR CODE ══════════════════════════════════════ --}}
    <div class="lt-qr-wrap anim d10" onclick="downloadQR()" title="Klik untuk unduh QR">
        <div class="lt-qr-img-frame">
            <img id="qrImage"
                 src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(url()->current()) }}&color=040f0f&bgcolor=ffffff&margin=5&qzone=1"
                 alt="QR Code"
                 class="lt-qr-img">
        </div>
        <p class="lt-qr-label">Scan to Share</p>
        <p class="lt-qr-hint">Klik untuk unduh PNG</p>
    </div>

    {{-- ══ FOOTER ══════════════════════════════════════ --}}
    <div class="lt-footer anim d10">
        <a href="{{ url('/') }}"
           style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;opacity:0.6;transition:opacity .2s;"
           onmouseover="this.style.opacity='1'"
           onmouseout="this.style.opacity='0.6'">
            <p class="lt-footer-txt">
                Powered by <span class="lt-footer-brand">MEDIATOOLS</span>
            </p>
        </a>
    </div>

</div>{{-- /lt-page --}}

{{-- Toast --}}
<div id="lt-view-toast"></div>

<script>
/* Share */
function sharePage() {
    const url  = window.location.href;
    const name = '{{ addslashes($profile['name']) }}';
    if (navigator.share) {
        navigator.share({ title: name, url }).catch(() => fallbackCopy(url));
    } else {
        fallbackCopy(url);
    }
}

function fallbackCopy(url) {
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(url)
            .then(() => showToast('Link berhasil disalin! 🚀'))
            .catch(() => legacyCopy(url));
    } else {
        legacyCopy(url);
    }
}

function legacyCopy(url) {
    const ta = document.createElement('textarea');
    ta.value = url; ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0;';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); showToast('Link berhasil disalin! 🚀'); }
    catch { showToast('Gagal menyalin link.', true); }
    document.body.removeChild(ta);
}

/* Download QR */
async function downloadQR() {
    const img  = document.getElementById('qrImage');
    const slug = '{{ Str::slug($profile['name']) }}';
    try {
        const res  = await fetch(img.src);
        const blob = await res.blob();
        const url  = URL.createObjectURL(blob);
        const a    = Object.assign(document.createElement('a'), { href: url, download: `QR-${slug}.png` });
        document.body.appendChild(a); a.click();
        document.body.removeChild(a); URL.revokeObjectURL(url);
        showToast('QR Code diunduh! 📥');
    } catch {
        showToast('Gagal mengunduh QR.', true);
    }
}

/* Toast */
let _tt;
function showToast(msg, isError = false) {
    const t = document.getElementById('lt-view-toast');
    t.textContent = msg;
    if (isError) { t.style.background = '#ef4444'; t.style.color = '#fff'; }
    t.classList.add('show');
    clearTimeout(_tt);
    _tt = setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
</body>
</html>