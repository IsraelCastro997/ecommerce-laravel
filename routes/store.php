<?php

use App\Http\Controllers\Checkout\CheckoutController;
use App\Livewire\Store\Catalog;
use App\Livewire\Store\Checkout;
use App\Livewire\Store\OrderConfirmation;
use App\Livewire\Store\ProductDetail;
use App\Livewire\Store\ShoppingCart;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Store\CatalogController;


    //Fetch store Products
    Route::prefix('storefront')->name('products.')->group(function (): void {
        Route::get('/products',[CatalogController::class,'filters'])->name('filters');

        Route::post('/products/loockup',[CatalogController::class,'lookup'])->name('lockup');
        Route::get('products/{product:slug}',[
            CatalogController::class,'show'
        ])->name('show');

    });


Route::name('store.')->group(function () {
    Route::livewire('/catalogo', Catalog::class)
        ->name('catalog');

    Route::livewire('/productos/{product}', ProductDetail::class)
        ->name('products.show');

    Route::livewire('/carrito', ShoppingCart::class)
        ->name('cart');


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
