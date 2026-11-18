<?php

use App\Http\Controllers\Checkout\CheckoutController;
use App\Livewire\Store\Catalog;
use App\Livewire\Store\Checkout;
use App\Livewire\Store\OrderConfirmation;
use App\Livewire\Store\ProductDetail;
use App\Livewire\Store\ShoppingCart;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Store\CatalogController;

Route::name('store.')->group(function () {
    Route::livewire('/catalogo', Catalog::class)
        ->name('catalog');

    Route::livewire('/productos/{product}', ProductDetail::class)
        ->name('products.show');

    Route::livewire('/carrito', ShoppingCart::class)
        ->name('cart');


    //Fetch store Products
    Route::get('/storefront/products', [CatalogController::class,'index'])->name('products.data');


    Route::middleware('auth')->group(function () {
        Route::view('/checkout/success', 'checkout.success')
            ->name('checkout.success');

        Route::view('/checkout/cancel', 'checkout.cancel')
            ->name('checkout.cancel');

        Route::livewire('/checkout', Checkout::class)
            ->name('checkout');

        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('checkout.store');

        Route::livewire(
            '/pedido-confirmado/{order}',
            OrderConfirmation::class
        )->name('orders.confirmation');
    });
});
