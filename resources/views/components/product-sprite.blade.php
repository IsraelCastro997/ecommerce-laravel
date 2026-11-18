@props(['product' => 'product'])
<svg {{ $attributes->class(['product-photo', 'catalog-sprite']) }} viewBox="0 0 768 512" preserveAspectRatio="xMidYMid meet" role="img" :aria-label="'Imagen no disponible: ' + ({{ $product }}?.name || 'Producto')">
    <g fill="none" stroke="#697366" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="329" y="187" width="110" height="88" rx="10" />
        <circle cx="362" cy="212" r="9" />
        <path d="m331 259 34-29 24 20 20-16 29 27" />
    </g>
    <text x="384" y="316" text-anchor="middle" fill="#596052" font-family="sans-serif" font-size="18">Imagen no disponible</text>
</svg>
