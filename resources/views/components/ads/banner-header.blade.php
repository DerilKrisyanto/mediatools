{{--
    SLOT: HEADER BANNER (728×90)
    Posisi : atas form tools, bawah page title
    Tampil : desktop only (disembunyikan di mobile via CSS)
    Prioritas: Opsional — impressi tinggi, CTR rendah
--}}

@php
    $adsEnabled = config('ads.enabled', false);
    $provider   = config('ads.provider', 'none');
    $slotActive = config('ads.slots.header', true);
@endphp

@if($adsEnabled && $slotActive && $provider !== 'none')
<div class="ads-container ads-container--header" aria-label="Advertisement" role="complementary">
    <div class="ads-label">Advertisement</div>

    @if($provider === 'adsterra')
    {{-- ══ ADSTERRA 728×90 ══ --}}
    <script type="text/javascript">
        atOptions = {
            'key'    : '8fe66f5ff481a8b5a447e9c2ad4b94af',
            'format' : 'iframe',
            'height' : 90,
            'width'  : 728,
            'params' : {}
        };
    </script>
    <script type="text/javascript"
        src="https://www.highperformanceformat.com/8fe66f5ff481a8b5a447e9c2ad4b94af/invoke.js">
    </script>

    @elseif($provider === 'adsense')
    {{-- ══ GOOGLE ADSENSE HEADER ══ --}}
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="{{ config('ads.adsense.client_id') }}"
         data-ad-slot="{{ config('ads.adsense.slots.header') }}"
         data-ad-format="auto"
         data-full-width-responsive="true">
    </ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    @endif

</div>
@endif