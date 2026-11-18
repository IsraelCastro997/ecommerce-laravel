@props(['title' => 'Refugio Outdoor', 'catalogProducts' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preload" href="/fonts/dm-sans-400.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/bricolage-grotesque-600.woff2" as="font" type="font/woff2" crossorigin>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Refugio Outdoor: equipo para explorar, campamento y senderismo.">
    <title>{{ $title }} · Refugio Outdoor</title>
    @if($catalogProducts !== null)<script type="application/json" id="storefront-products">@json($catalogProducts)</script>@endif
    @vite('resources/js/app.js')
    @livewireStyles
</head>
<body class="refugio" x-data="themePicker">
    <a class="skip-navigation" href="#contenido">Saltar al contenido</a>
    <div class="store-announcement">Tienda de demostración · Precios y disponibilidad ilustrativos</div>
    <header class="store-header">
        <div class="store-wrap header-inner">
            <a class="store-mark" href="{{ route('home') }}" aria-label="Refugio Outdoor, inicio"><x-store-icon name="mountain" size="31" /><span>REFUGIO<small>OUTDOOR</small></span></a>
            <nav class="main-links" aria-label="Navegación principal"><a href="{{ route('store.catalog') }}">Catálogo</a><a href="{{ route('home') }}#categorias">Categorías</a><a href="{{ route('home') }}#ayuda">Ayuda</a></nav>
            <div class="store-actions" x-data>
                <select aria-label="Moneda" :value="$store.shop.currency" @change="$store.shop.setCurrency($event.target.value)"><option>MXN</option><option>USD</option></select>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ auth()->check() ? 'Mi cuenta' : 'Ingresar' }}</a>
                <a class="icon-button bag-button" href="{{ route('store.cart') }}" aria-label="Ver carrito"><x-store-icon name="bag" size="21" /> Bolsa <span x-text="$store.shop.count">0</span></a>
            </div>
        </div>
    </header>
    <main id="contenido" tabindex="-1">{{ $slot }}</main>
    <footer class="store-footer" id="ayuda"><div class="store-wrap"><div><strong>REFUGIO OUTDOOR</strong><p>Equipo para tu próximo camino.</p></div><nav aria-label="Ayuda"><a href="{{ route('home') }}#contacto">Contacto</a><a href="{{ route('store.catalog') }}">Explorar catálogo</a><a href="{{ route('login') }}">Mi cuenta</a></nav></div></footer>
    <div class="catalog-toast" role="status" aria-live="polite" x-data="{ timer: null }" x-show="$store.shop.notice" x-cloak x-init="$watch('$store.shop.notice', value => { if (value) { clearTimeout(timer); timer = setTimeout(() => $store.shop.notice = '', 6000) } })"><span class="catalog-toast-check"><x-store-icon name="check" size="19" /></span><span x-text="$store.shop.notice"></span><a href="{{ route('store.cart') }}">Ver bolsa</a><button type="button" @click="$store.shop.notice = ''" aria-label="Cerrar aviso"><x-store-icon name="close" size="17" /></button></div>
    @livewireScripts
</body>
</html>
