@extends('layouts.app')

@section('title', 'Buat Link in Bio Gratis — Bio Link Page Builder | MediaTools')
@section('meta_description', 'Buat halaman bio link profesional untuk Instagram, TikTok, dan semua sosmed. Satu halaman untuk semua tautan penting Anda. Gratis, tanpa coding.')
@section('meta_keywords', 'buat link in bio gratis, linktree gratis indonesia, bio link page, link in bio instagram, satu halaman semua link')

@push('json_ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "LinkTree Builder — MediaTools",
  "url": "https://mediatools.cloud/linktree",
  "applicationCategory": "SocialNetworkingApplication",
  "operatingSystem": "Any",
  "offers": { "@type": "Offer", "price": "0", "priceCurrency": "IDR" },
  "description": "Buat halaman bio link untuk Instagram, TikTok, YouTube. Gratis, tanpa coding.",
  "inLanguage": "id"
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/linktree.css') }}">
@endpush

@section('content')

{{-- Hidden utility inputs --}}
<input type="hidden" id="checkPlanUrl" value="{{ route('tools.linktree.checkplan') }}">

<div class="lt-shell">
<div class="max-w-6xl mx-auto px-4 sm:px-6">

    {{-- ═══ TOP BAR ═══ --}}
    <div class="lt-topbar">
        <div>
            <div style="display:inline-flex;align-items:center;gap:6px;padding:3px 12px;background:rgba(163,230,53,0.1);border:1px solid rgba(163,230,53,0.2);border-radius:99px;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:#a3e635;margin-bottom:10px;">
                <span style="width:6px;height:6px;border-radius:50%;background:#a3e635;animation:none;display:inline-block;"></span>
                Premium Tool
            </div>
            <h1 class="lt-title">LinkTree <span>Builder.</span></h1>
            <p class="lt-subtitle">Satu halaman untuk semua tautan penting Anda.</p>
        </div>

        @auth
        <button onclick="handleCreateStep()" class="lt-btn-primary">
            @if($userLinktree && $userLinktree->is_active)
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Linktree Saya</span>
            @else
                <i class="fa-solid fa-plus"></i>
                <span>Buat Linktree</span>
            @endif
        </button>
        @else
        <a href="{{ route('login') }}" class="lt-btn-primary">
            <i class="fa-solid fa-lock"></i>
            <span>Masuk untuk Mulai</span>
        </a>
        @endauth
    </div>

    {{-- ═══ STATS ═══ --}}
    <div class="lt-stats">
        <div class="lt-stat-card">
            <div class="lt-stat-label">Total Halaman Aktif</div>
            <div class="lt-stat-value">{{ $items->count() }}</div>
            <div class="lt-stat-sub">Linktree aktif saat ini</div>
        </div>
        <div class="lt-stat-card">
            <div class="lt-stat-label">Total Pengunjung</div>
            <div class="lt-stat-value">{{ number_format($items->sum('visitors')) }}</div>
            <div class="lt-stat-sub">Seluruh halaman</div>
        </div>
        <div class="lt-stat-card">
            <div class="lt-stat-label">Status Saya</div>
            @if($userLinktree && $userLinktree->is_active)
                <div class="lt-stat-value" style="font-size:1.1rem;margin-top:4px;">
                    <span class="lt-badge lt-badge-active">● Aktif</span>
                </div>
                <div class="lt-stat-sub">Berakhir: {{ $userLinktree->expired_at?->format('d M Y') ?? '—' }}</div>
            @else
                <div class="lt-stat-value" style="font-size:1.1rem;margin-top:4px;">
                    <span class="lt-badge lt-badge-pending">— Belum Ada</span>
                </div>
                <div class="lt-stat-sub">Buat sekarang</div>
            @endif
        </div>
        <div class="lt-stat-card">
            <div class="lt-stat-label">Kunjungan Saya</div>
            <div class="lt-stat-value">{{ number_format($userLinktree?->visitors ?? 0) }}</div>
            <div class="lt-stat-sub">Pengunjung halaman Anda</div>
        </div>
    </div>

    {{-- ═══ TABLE ═══ --}}
    <div class="lt-table-wrap">
        <table class="lt-table">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>ID Linktree</th>
                    <th>Template</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Pengunjung</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    {{-- User --}}
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img src="{{ $item->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($item->name).'&background=a3e635&color=0f172a&bold=true&size=80' }}"
                                 class="lt-avatar" alt="{{ $item->name }}">
                            <div>
                                <div style="font-weight:700;font-size:13px;">{{ $item->name }}</div>
                                <div class="lt-username">{{ $item->username }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Unique ID --}}
                    <td>
                        <span class="lt-unique-id">{{ $item->unique_id }}</span>
                    </td>

                    {{-- Template --}}
                    <td>
                        <span class="lt-tpl-badge">
                            @php $tplNames = ['dark'=>'Dark','light'=>'Terang','neon'=>'Neon']; @endphp
                            {{ $tplNames[$item->page_template ?? 'dark'] ?? 'Dark' }}
                        </span>
                    </td>

                    {{-- Status --}}
                    <td style="text-align:center;">
                        @if($item->is_active)
                            <span class="lt-badge lt-badge-active">● Aktif</span>
                        @else
                            <span class="lt-badge lt-badge-pending">○ Pending</span>
                        @endif
                    </td>

                    {{-- Visitors --}}
                    <td style="text-align:center;">
                        <span style="background:rgba(255,255,255,0.05);padding:4px 12px;border-radius:99px;font-size:11px;font-weight:700;">
                            <i class="fa-solid fa-eye" style="margin-right:5px;color:#a3e635;font-size:9px;"></i>{{ number_format($item->visitors) }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td style="text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                            <a href="{{ route('tools.linktree.show', $item->unique_id) }}"
                               target="_blank"
                               class="lt-action-btn lt-action-view">
                                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px;"></i>
                                Lihat
                            </a>
                            @auth
                            @if($item->user_id === Auth::id())
                            <button type="button"
                                    onclick='handleEditClick("{{ urlencode(json_encode($item)) }}")'
                                    class="lt-action-btn lt-action-edit">
                                <i class="fa-solid fa-pen" style="font-size:10px;"></i>
                                Edit
                            </button>
                            @endif
                            @endauth
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="lt-empty">
                            <div class="lt-empty-icon">
                                <i class="fa-solid fa-link"></i>
                            </div>
                            <p style="font-weight:700;color:rgba(255,255,255,0.5);font-size:14px;margin-bottom:4px;">Belum ada linktree aktif.</p>
                            <p style="font-size:12px;color:var(--lt-muted);">Jadilah yang pertama membuat halaman digital Anda.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</div>

{{-- ════════════════════════════════════
     MODAL 1: CHOOSE PLAN
════════════════════════════════════ --}}
<div id="planModal" class="lt-modal-backdrop">
    <div class="lt-modal" style="max-width:640px;">
        <div class="lt-modal-header">
            <div class="lt-modal-title">
                <i class="fa-solid fa-rocket"></i>
                Pilih Paket Linktree
            </div>
            <button onclick="closeAllModals()" class="lt-modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="lt-modal-body">

            <p style="color:var(--lt-muted);font-size:13px;margin-bottom:20px;">
                Aktifkan halaman digital profesional Anda dengan paket yang sesuai kebutuhan.
            </p>

            <div class="lt-plan-grid">

                {{-- Starter --}}
                <div class="lt-plan-card" data-plan="starter" onclick="selectPlan('starter')">
                    <div class="lt-plan-check"><i class="fa-solid fa-check"></i></div>
                    <div class="lt-plan-name">Starter</div>
                    <div class="lt-plan-period">1 Bulan Akses</div>
                    <div class="lt-plan-price"><small>Rp</small>19.900</div>
                    <div class="lt-plan-permonth">Rp 19.900 / bulan</div>
                    <div class="lt-plan-features">
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>1 Halaman Linktree</div>
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>3 Template Pilihan</div>
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>QR Code Otomatis</div>
                    </div>
                </div>

                {{-- Best Value --}}
                <div class="lt-plan-card" data-plan="best_value" onclick="selectPlan('best_value')" style="background:rgba(163,230,53,0.04);">
                    <div class="lt-plan-hot">HOT 🔥</div>
                    <div class="lt-plan-check"><i class="fa-solid fa-check"></i></div>
                    <div class="lt-plan-name">Best Value</div>
                    <div class="lt-plan-period">6 Bulan Akses</div>
                    <div class="lt-plan-price"><small>Rp</small>89.000</div>
                    <div class="lt-plan-permonth">~Rp 14.833 / bulan</div>
                    <div class="lt-plan-features">
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>Semua fitur Starter</div>
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>Badge Verified</div>
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>Analitik Pengunjung</div>
                    </div>
                </div>

                {{-- Business --}}
                <div class="lt-plan-card" data-plan="business" onclick="selectPlan('business')">
                    <div class="lt-plan-check"><i class="fa-solid fa-check"></i></div>
                    <div class="lt-plan-name">Business</div>
                    <div class="lt-plan-period">12 Bulan Akses</div>
                    <div class="lt-plan-price"><small>Rp</small>149.000</div>
                    <div class="lt-plan-permonth">~Rp 12.416 / bulan</div>
                    <div class="lt-plan-features">
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>Semua fitur Best Value</div>
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>Prioritas Support</div>
                        <div class="lt-plan-feat"><i class="fa-solid fa-check"></i>Link tak terbatas</div>
                    </div>
                </div>

            </div>

            <button onclick="closeAllModals()" style="display:block;margin:0 auto;background:none;border:none;color:var(--lt-muted);font-size:12px;cursor:pointer;font-family:inherit;padding:8px;">
                Mungkin Nanti
            </button>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════
     MODAL 2: CREATE / EDIT FORM
════════════════════════════════════ --}}
<div id="createModal" class="lt-modal-backdrop">
    <div class="lt-modal" style="max-width:580px;">
        <div class="lt-modal-header">
            <div class="lt-modal-title">
                <i class="fa-solid fa-magic-wand-sparkles"></i>
                Detail Linktree
            </div>
            <button onclick="closeAllModals()" class="lt-modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="lt-modal-body">

            <form id="linktreeForm" data-store-url="{{ route('tools.linktree.store') }}">
                @csrf
                <input type="hidden" name="plan_type"      id="selected_plan_input">
                <input type="hidden" name="page_template"  id="page_template_input" value="dark">

                {{-- Avatar --}}
                <div style="display:flex;align-items:center;gap:16px;padding:16px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:18px;margin-bottom:20px;">
                    <div style="position:relative;">
                        <img id="avatarPreview"
                             src="https://ui-avatars.com/api/?name=User&background=a3e635&color=0f172a&bold=true&size=80"
                             style="width:72px;height:72px;border-radius:18px;object-fit:cover;border:2px solid rgba(163,230,53,0.3);">
                        <label for="avatarInput"
                               style="position:absolute;inset:0;background:rgba(0,0,0,0.6);border-radius:18px;display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:0;transition:opacity .2s;"
                               onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                            <i class="fa-solid fa-camera" style="color:white;font-size:16px;"></i>
                        </label>
                        <input type="file" id="avatarInput" accept="image/*" style="display:none;">
                        <input type="hidden" id="avatar_base64" name="avatar_base64">
                    </div>
                    <div>
                        <p style="font-weight:700;font-size:13px;color:#a3e635;margin-bottom:3px;">Foto Profil</p>
                        <p style="font-size:11px;color:var(--lt-muted);line-height:1.5;">Upload foto terbaik Anda.<br>PNG/JPG · Maks 2MB · Rekomendasi 400×400px</p>
                    </div>
                </div>

                {{-- Name + Username --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="lt-label" for="name">Nama Tampilan</label>
                        <input type="text" id="name" name="name" required class="lt-input" placeholder="Contoh: Budi Santoso">
                    </div>
                    <div>
                        <label class="lt-label" for="username">Username</label>
                        <div class="lt-input-icon-wrap" style="padding:11px 14px;">
                            <span style="color:var(--lt-muted);font-size:13px;font-weight:700;">@</span>
                            <input type="text" id="username" name="username" required placeholder="namaanda" style="font-size:13px;">
                        </div>
                    </div>
                </div>

                {{-- Bio --}}
                <div style="margin-bottom:20px;">
                    <label class="lt-label" for="bio">Bio Singkat</label>
                    <textarea id="bio" name="bio" rows="2" class="lt-input lt-textarea" placeholder="Ceritakan tentang diri Anda…"></textarea>
                </div>

                {{-- Template Selector --}}
                <div style="margin-bottom:20px;padding:16px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.05);border-radius:18px;">
                    <label class="lt-label" style="margin-bottom:12px;">Pilih Tampilan Halaman</label>
                    <div class="lt-tpl-grid">

                        {{-- Dark --}}
                        <div class="lt-tpl-option active" data-tpl="dark" onclick="selectTemplate('dark')">
                            <div class="lt-tpl-check chk"><i class="fa-solid fa-check"></i></div>
                            <div class="lt-tpl-preview tpl-thumb-dark">
                                <div style="width:28px;height:28px;border-radius:50%;background:rgba(163,230,53,0.3);border:2px solid #a3e635;margin-bottom:4px;"></div>
                                <div style="width:50px;height:3px;background:rgba(255,255,255,0.3);border-radius:99px;"></div>
                                <div style="width:36px;height:2px;background:rgba(163,230,53,0.4);border-radius:99px;margin-top:3px;"></div>
                                <div style="width:44px;height:6px;background:rgba(255,255,255,0.08);border-radius:4px;margin-top:4px;"></div>
                            </div>
                            <div class="lt-tpl-label">Dark</div>
                        </div>

                        {{-- Light --}}
                        <div class="lt-tpl-option" data-tpl="light" onclick="selectTemplate('light')">
                            <div class="lt-tpl-check chk"><i class="fa-solid fa-check"></i></div>
                            <div class="lt-tpl-preview tpl-thumb-light">
                                <div style="width:28px;height:28px;border-radius:50%;background:#e2e8f0;border:2px solid #a3e635;margin-bottom:4px;"></div>
                                <div style="width:50px;height:3px;background:#334155;border-radius:99px;"></div>
                                <div style="width:36px;height:2px;background:#94a3b8;border-radius:99px;margin-top:3px;"></div>
                                <div style="width:44px;height:6px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;margin-top:4px;"></div>
                            </div>
                            <div class="lt-tpl-label" style="background:#f8fafc;color:#64748b;">Terang</div>
                        </div>

                        {{-- Neon --}}
                        <div class="lt-tpl-option" data-tpl="neon" onclick="selectTemplate('neon')">
                            <div class="lt-tpl-check chk"><i class="fa-solid fa-check"></i></div>
                            <div class="lt-tpl-preview tpl-thumb-neon">
                                <div style="width:28px;height:28px;border-radius:50%;background:rgba(139,92,246,0.4);border:2px solid #8b5cf6;margin-bottom:4px;box-shadow:0 0 10px rgba(139,92,246,0.6);"></div>
                                <div style="width:50px;height:3px;background:rgba(255,255,255,0.6);border-radius:99px;"></div>
                                <div style="width:36px;height:2px;background:rgba(139,92,246,0.8);border-radius:99px;margin-top:3px;"></div>
                                <div style="width:44px;height:6px;background:rgba(139,92,246,0.15);border:1px solid rgba(139,92,246,0.3);border-radius:4px;margin-top:4px;"></div>
                            </div>
                            <div class="lt-tpl-label">Neon</div>
                        </div>

                    </div>
                </div>

                {{-- Links & Socials --}}
                <div style="border-top:1px solid rgba(255,255,255,0.05);padding-top:20px;margin-bottom:20px;">
                    <p class="lt-label" style="margin-bottom:14px;">Koneksi & Tautan</p>

                    <div style="margin-bottom:10px;">
                        <div class="lt-input-icon-wrap">
                            <i class="fa-solid fa-globe" style="color:#60a5fa;font-size:13px;flex-shrink:0;width:18px;text-align:center;"></i>
                            <input type="url" name="web_url" placeholder="https://website-anda.com">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                        <div class="lt-input-icon-wrap">
                            <i class="fa-brands fa-tiktok" style="color:white;font-size:13px;flex-shrink:0;width:18px;text-align:center;"></i>
                            <input type="text" name="tt_user" id="tt_user" placeholder="TikTok Username">
                        </div>
                        <div class="lt-input-icon-wrap">
                            <i class="fa-brands fa-instagram" style="color:#ec4899;font-size:13px;flex-shrink:0;width:18px;text-align:center;"></i>
                            <input type="text" name="ig_user" id="ig_user" placeholder="Instagram Username">
                        </div>
                    </div>

                    <div class="lt-input-icon-wrap">
                        <i class="fa-brands fa-whatsapp" style="color:#4ade80;font-size:13px;flex-shrink:0;width:18px;text-align:center;"></i>
                        <input type="number" name="wa_number" id="wa_number" placeholder="628123456789 (format internasional)">
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" id="submitBtn" class="lt-submit-btn">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Generate Linktree</span>
                </button>

            </form>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="lt-toast" style="font-family:'Plus Jakarta Sans',sans-serif;">
    <div id="lt-toast-icon" style="width:28px;height:28px;background:rgba(0,0,0,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;"></div>
    <span id="lt-toast-msg"></span>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/linktree.js') }}"></script>
<script src="https://app.{{ config('services.midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>
@endpush