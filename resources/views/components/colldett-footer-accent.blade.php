@props([
    'wrapperClass' => '',
])

{{-- Letterhead footer accent: green bar ending in a point, then gold / green / gold chevrons (gaps = page bg). --}}
<div class="colldett-footer-accent {{ $wrapperClass }}" aria-hidden="true">
    <svg
        class="colldett-footer-accent__svg"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 112 12"
        preserveAspectRatio="xMinYMid meet"
    >
        <polygon fill="#0f3326" points="0,0 50,0 58,6 50,12 0,12"/>
        <polygon fill="#c9a227" points="64,2 72,6 64,10"/>
        <polygon fill="#0f3326" points="78,2 86,6 78,10"/>
        <polygon fill="#c9a227" points="92,2 100,6 92,10"/>
    </svg>
</div>
