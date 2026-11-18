<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {

        return response()->json([
            'message' => 'Falta validar el carrito y crear el pedido con sus importes calculados en el servidor. No se ha iniciado ningún cobro.',
        ], 501);
    }


    private function checkoutOrder(Request $request, Order $order): JsonResponse
    {
        $totalCentavos = (int) bcmul($order->grand_total, '100', 0);

        $checkout = $request->user()->checkoutCharge(
            $totalCentavos,
            'Pedido '.$order->order_number,
            1,
            [
                'automatic_tax' => ['enabled' => false],
                'success_url' => route('store.checkout.success')
                    .'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('store.checkout.cancel'),
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ],
        );

        return response()->json([
            'checkout_url' => $checkout->asStripeCheckoutSession()->url,
        ]);
    }
}
