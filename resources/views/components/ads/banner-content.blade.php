{{--
    SLOT: IN-CONTENT — NATIVE BANNER
    Posisi   : tengah form, sebelum tombol generate
    Format   : Native Banner (menyatu dengan desain, tidak terasa seperti iklan)
    Prioritas: WAJIB — user masih engaged, perhatian tinggi
--}}

@php
    $adsEnabled = config('ads.enabled', false);
    $provider   = config('ads.provider', 'none');
    $slotActive = config('ads.slots.content', true);
@endphp

@if($adsEnabled && $slotActive && $provider !== 'none')
<div class="ads-container ads-container--content" aria-label="Advertisement" role="complementary">
    <div class="ads-label">Advertisement</div>

    @if($provider === 'adsterra')
    {{-- ══ ADSTERRA NATIVE BANNER ══ --}}
    <script async="async"
        data-cfasync="false"
        src="https://pl29114359.profitablecpmratenetwork.com/dbcfe45533161209961378c9e1236516/invoke.js">
    </script>
    <div id="container-dbcfe45533161209961378c9e1236516"></div>

    @elseif($provider === 'adsense')
    {{-- ══ GOOGLE ADSENSE IN-ARTICLE ══ --}}
    <ins class="adsbygoogle"
         style="display:block; text-align:center;"
         data-ad-layout="in-article"
         data-ad-format="fluid"
         data-ad-client="{{ config('ads.adsense.client_id') }}"
         data-ad-slot="{{ config('ads.adsense.slots.content') }}">
    </ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    @endif

</div>
@endif