{{--
    SLOT: RESULT BANNER — PRIORITAS TERTINGGI (300×250)
    ════════════════════════════════════════════════════
    Posisi: TEPAT setelah output/hasil generate tampil
            - Setelah preview invoice muncul
            - Setelah QR code render
            - Setelah signature dibuat
            - Setelah file terkonversi

    Logika: User baru saja MENDAPAT nilai dari tools
            → perhatian bebas → momen terbaik untuk klik iklan
    ════════════════════════════════════════════════════
--}}

@php
    $adsEnabled = config('ads.enabled', false);
    $provider   = config('ads.provider', 'none');
    $slotActive = config('ads.slots.result', true);
@endphp

@if($adsEnabled && $slotActive && $provider !== 'none')
<div class="ads-container ads-container--result"
     aria-label="Advertisement" role="complementary">
    <div class="ads-label">Advertisement</div>

    @if($provider === 'adsterra')
    {{-- ══ ADSTERRA 300×250 ══ --}}
    <script type="text/javascript">
        atOptions = {
            'key'    : '941b949915f664c0f4af0d9ccc1cc5e8',
            'format' : 'iframe',
            'height' : 250,
            'width'  : 300,
            'params' : {}
        };
    </script>
    <script type="text/javascript"
        src="https://www.highperformanceformat.com/941b949915f664c0f4af0d9ccc1cc5e8/invoke.js">
    </script>

    @elseif($provider === 'adsense')
    {{-- ══ GOOGLE ADSENSE RESULT ══ --}}
    <ins class="adsbygoogle"
         style="display:inline-block;width:300px;height:250px"
         data-ad-client="{{ config('ads.adsense.client_id') }}"
         data-ad-slot="{{ config('ads.adsense.slots.result') }}">
    </ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    @endif

</div>
@endif