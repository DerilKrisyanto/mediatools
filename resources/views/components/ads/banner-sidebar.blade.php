{{--
    SLOT: SIDEBAR STICKY (300×250)
    Posisi   : kolom kanan form, sticky saat scroll
    Tampil   : desktop only (lg ke atas)
    Prioritas: Disarankan — impressi panjang karena sticky
--}}

@php
    $adsEnabled = config('ads.enabled', false);
    $provider   = config('ads.provider', 'none');
    $slotActive = config('ads.slots.sidebar', true);
@endphp

@if($adsEnabled && $slotActive && $provider !== 'none')
<div class="ads-container ads-container--sidebar hidden lg:block"
     aria-label="Advertisement" role="complementary">
    <div class="ads-label">Advertisement</div>
    <div class="ads-sidebar-sticky">

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
        {{-- ══ GOOGLE ADSENSE SIDEBAR ══ --}}
        <ins class="adsbygoogle"
             style="display:inline-block;width:300px;height:250px"
             data-ad-client="{{ config('ads.adsense.client_id') }}"
             data-ad-slot="{{ config('ads.adsense.slots.sidebar') }}">
        </ins>
        <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
        @endif

    </div>
</div>
@endif