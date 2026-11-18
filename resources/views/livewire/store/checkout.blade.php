<div class="store-wrap blade-page">

    <p class="eyebrow">REVISA TU EQUIPO</p><h1>Resumen de compra</h1>

    <x-cart-preview :checkout="true" />

    <section id="checkout-contact" class="blade-panel" x-data="checkoutPreview(@js(route('store.checkout.store')), @js(auth()->id()))" x-init="email = @js(auth()->user()?->email ?? '')">

        <h2 class="text-xl font-bold">Datos de contacto</h2>

        <form class="blade-form mt-4" method="POST" action="{{ route('store.checkout.store') }}" @submit.prevent="prepare()">

            @csrf

            <label>Correo electrónico<input id="checkout-email" name="email" type="email" required autocomplete="email" x-model="email" placeholder="tu@correo.com"></label>

            <input type="hidden" name="currency" :value="$store.shop.currency">

            <template x-for="(item,index) in previewItems" :key="index"><div>

                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id ?? ''">

                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">

            </div></template>

            <p class="page-note">Los importes son estimaciones. Se envían únicamente IDs y cantidades de productos, correo y moneda. El servidor debe confirmar precios, existencias e IVA.</p>

            <button class="store-button primary" type="submit" :disabled="!!order || busy || $store.shop.cartLoading || !!$store.shop.cartError || !$store.shop.count" x-text="order ? 'Pre-pedido generado' : busy ? 'Enviando…' : 'Generar pre-pedido'"></button>

            <p role="status" aria-live="polite" x-text="feedback"></p>



        </form>

        <section class="mt-5" x-show="order" x-cloak><h3 class="text-lg font-bold" x-text="order?.order_number"></h3><p x-text="order ? $store.shop.price(Number(order.grand_total),order.currency) : ''"></p><div class="blade-actions mt-4"><button class="store-button primary" type="button" @click="pay()" :disabled="paymentBusy || order?.status !== 'pending_payment'" x-text="paymentBusy ? 'Abriendo Stripe…' : 'Pagar con Stripe'"></button><a class="store-button" :href="order ? '/pedido-confirmado/'+order.id : '#'">Consultar pedido</a><button type="button" class="text-button" @click="newOrder()" :disabled="paymentBusy">Crear otro pre-pedido</button></div></section>
        <p class="page-note mt-5">Se guardará un pre-pedido pendiente con precios, IVA y disponibilidad confirmados por el servidor. Esta acción no inicia ningún pago.</p>

    </section>

</div>

