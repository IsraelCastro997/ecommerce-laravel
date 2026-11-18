@props(['cancelled' => false])
<section class="checkout-result-page store-wrap">
    <nav class="catalog-breadcrumb" aria-label="Ruta de navegación"><a href="{{ route('home') }}">Inicio</a><x-store-icon name="chevron" size="12" /><a href="{{ route('store.cart') }}">Tu bolsa</a><x-store-icon name="chevron" size="12" /><span aria-current="page">{{ $cancelled ? 'Regreso del pago' : 'Estado del pago' }}</span></nav>
    <div class="checkout-result-card">
        <div class="checkout-result-landscape" role="img" aria-label="Campamento entre montañas al amanecer"><span><x-store-icon name="mountain" size="26" /> Nos vemos afuera.</span></div>
        <div class="checkout-result-content">
            <span class="checkout-result-symbol" aria-hidden="true">
                @if($cancelled)<x-store-icon name="bag" size="29" />
                @else<svg width="29" height="29" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>@endif
            </span>
            @if($cancelled)
                <h1>Tu próxima salida<br>puede esperar.</h1>
                <p>Has salido de la pantalla de pago. Puedes revisar tu bolsa y volver cuando estés listo.</p>
                <div class="checkout-result-status"><strong>Has vuelto a Refugio</strong><span>La bolsa de este navegador se conserva. Puedes consultar tus pedidos para conocer el estado de una compra.</span></div>
                <div class="checkout-result-actions"><a class="catalog-dark-button" href="{{ route('store.cart') }}"><x-store-icon name="bag" size="18" /> Revisar mi bolsa</a><a class="catalog-text-link" href="{{ route('store.checkout') }}">Volver al checkout <x-store-icon name="arrow" size="17" /></a></div>
            @else
                <h1>Un paso más<br>hacia tu aventura.</h1>
                <p>Has regresado de la pantalla de pago. El resultado de tu compra se mostrará cuando el servidor confirme su estado.</p>
                <div class="checkout-result-status" role="status"><strong>Pendiente de verificación</strong><span>Esta pantalla todavía no acredita un pago aprobado. Consulta tus pedidos antes de iniciar otro intento.</span></div>
                <div class="checkout-result-actions"><a class="catalog-dark-button" href="{{ route('account.orders.index') }}">Consultar mis pedidos <x-store-icon name="arrow" size="17" /></a><a class="catalog-text-link" href="{{ route('store.catalog') }}">Seguir explorando</a></div>
            @endif
        </div>
    </div>
</section>
