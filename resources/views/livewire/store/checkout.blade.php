<div class="store-wrap blade-page">
    <p class="eyebrow">REVISA TU EQUIPO</p><h1>Resumen de compra</h1>
    <x-cart-preview />
    <section class="blade-panel" x-data="checkoutPreview(@js(route('store.checkout.store')))">
        <h2 class="text-xl font-bold">Datos de contacto</h2>
        <form class="blade-form mt-4" method="POST" action="{{ route('store.checkout.store') }}" @submit.prevent="prepare()">
            @csrf
            <label>Correo electrónico<input name="email" type="email" required autocomplete="email" x-model="email" placeholder="tu@correo.com"></label>
            <input type="hidden" name="currency" :value="$store.shop.currency">
            <template x-for="(item,index) in previewItems" :key="index"><div>
                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id ?? ''">
                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
            </div></template>
            <p class="page-note">Los importes son estimaciones. Se envían únicamente IDs y cantidades de productos, correo y moneda. El servidor debe confirmar precios, existencias e IVA.</p>
            <button class="store-button primary" type="submit" :disabled="busy || !$store.shop.count" x-text="busy ? 'Enviando…' : 'Solicitar compra al servidor'"></button>
            <p role="status" aria-live="polite" x-text="feedback"></p>
            <details><summary>Productos que se enviarían</summary><p class="page-note">Los IDs nulos indican productos visuales de ejemplo todavía sin conexión con MariaDB.</p><pre class="overflow-auto text-sm p-3" x-text="JSON.stringify(previewItems, null, 2)"></pre></details>
        </form>
        <div class="blade-status mt-5"><strong>Pago deshabilitado</strong><p>La ruta POST está preparada. El controlador aún no calcula ni genera pedidos y responde explícitamente que falta esa implementación.</p></div>
    </section>
</div>
