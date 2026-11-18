@props(['product' => 'product'])
<svg {{ $attributes->class(['product-photo', 'catalog-sprite']) }} viewBox="0 0 768 512" preserveAspectRatio="xMidYMid meet" role="img" :aria-label="{{ $product }}.name" x-id="['product-sprite-clip']">
    <defs><clipPath :id="$id('product-sprite-clip')"><rect width="768" height="512" /></clipPath></defs>
    <g :clip-path="'url(#' + $id('product-sprite-clip') + ')'">
        <image href="/store/products.png" width="1536" height="1024" :x="({{ $product }}.image || '').includes('right') ? -768 : 0" :y="({{ $product }}.image || '').includes('bottom') ? -512 : 0" />
    </g>
</svg>
