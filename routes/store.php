<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Store\Catalog;
use App\Livewire\Store\ProductDetail;
use App\Livewire\Store\ShoppingCart;
use App\Livewire\Store\Checkout;
use App\Livewire\Store\OrderConfirmation;

Route::name('store.')->group(function () {

    Route::livewire('/catalogo', Catalog::class)
        ->name('catalog');

    Route::livewire('/productos/{product}', ProductDetail::class)
        ->name('products.show');

    Route::livewire('/carrito', ShoppingCart::class)
        ->name('cart');

    Route::livewire('/checkout', Checkout::class)
        ->name('checkout');

    Route::livewire(
        '/pedido-confirmado/{order}',
        OrderConfirmation::class
    )->name('orders.confirmation');
});