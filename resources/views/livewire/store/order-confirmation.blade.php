<section class="store-wrap blade-page" @if($order->status === 'pending_payment') wire:poll.3s.visible="refreshStatus" @endif>
 <div class="blade-panel" x-data="checkoutPreview('', @js(auth()->id()))" x-init="order = @js($order->only(['id','order_number','status','currency','grand_total']))">
 <h1>Pedido {{ $order->order_number }}</h1>
 <p class="ro-notice" role="status">{{ ['paid'=>'Pago confirmado','pending_payment'=>'Pendiente de pago o confirmación','expired'=>'La sesión de pago ha expirado'][$order->status] ?? $order->status }}</p>
 <p>Correo: {{ $order->customer_email }}</p>
 <div class="blade-table-wrap"><table class="blade-table"><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Total</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $item->name_snapshot }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_price }} {{ $order->currency }}</td><td>{{ $item->line_total }} {{ $order->currency }}</td></tr>@endforeach</tbody></table></div>
 <dl class="my-4"><dt>Subtotal</dt><dd>{{ $order->subtotal }} {{ $order->currency }}</dd><dt>IVA</dt><dd>{{ $order->tax_total }} {{ $order->currency }}</dd><dt>Total</dt><dd class="font-bold">{{ $order->grand_total }} {{ $order->currency }}</dd></dl>
 @if($order->status === 'paid')<p>El pago fue confirmado por Stripe. No necesitas volver a pagar.</p>
 @elseif($order->status === 'pending_payment')<p>La confirmación se actualiza automáticamente cuando Stripe notifica al servidor.</p><button type="button" class="store-button primary mt-4" @click="pay()" :disabled="paymentBusy" x-text="paymentBusy ? 'Abriendo Stripe…' : 'Pagar con Stripe'"></button><p role="alert" x-text="feedback"></p>
 @else<p>Consulta el estado de tu pedido antes de iniciar una nueva compra.</p>@endif
 <a class="store-button mt-4" href="{{ route('store.catalog') }}">Volver al catálogo</a>
 </div>
</section>
