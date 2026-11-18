<div class="store-wrap blade-page">
    <p class="eyebrow">REVISA TU EQUIPO</p><h1>Resumen de compra</h1>
    <x-cart-preview />
    <section class="blade-panel" x-data="checkoutPreview(@js(route('store.checkout.store')))" x-init="email = @js(auth()->user()?->email ?? '')">
        <h2 class="text-xl font-bold">Datos de contacto</h2>
        <form class="blade-form mt-4" method="POST" action="{{ route('store.checkout.store') }}" @submit.prevent="prepare()">
            @csrf
            <label>Correo electrÃ³nico<input name="email" type="email" required autocomplete="email" x-model="email" placeholder="tu@correo.com"></label>
            <input type="hidden" name="currency" :value="$store.shop.currency">
            <template x-for="(item,index) in previewItems" :key="index"><div>
                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id ?? ''">
                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
            </div></template>
            <p class="page-note">Los importes son estimaciones. Se envÃ­an Ãºnicamente IDs y cantidades de productos, correo y moneda. El servidor debe confirmar precios, existencias e IVA.</p>
            <button class="store-button primary" type="submit" :disabled="busy || !$store.shop.count" x-text="busy ? 'Enviandoâ€¦' : 'Generar pre-pedido'"></button>
            <p role="status" aria-live="polite" x-text="feedback"></p>

        </form>
        <p class="page-note mt-5">Se guardará un pre-pedido pendiente con precios, IVA y disponibilidad confirmados por el servidor. Esta acción no inicia ningún pago.</p>
    </section>
</div>
