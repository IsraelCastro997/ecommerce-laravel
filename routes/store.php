<?php


use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Store\CatalogController;
use App\Livewire\Store\Catalog;
use App\Livewire\Store\Checkout;
use App\Livewire\Store\OrderConfirmation;
use App\Livewire\Store\ProductDetail;
use App\Livewire\Store\ShoppingCart;
use Illuminate\Support\Facades\Route;



    //Fetch store Products
    Route::prefix('storefront')->name('products.')->group(function (): void {
        Route::get('/products', [CatalogController::class, 'index'])->name('index');
        Route::get('/products/by-id/{product}', [CatalogController::class, 'show'])->name('by-id');
        Route::get('/filters', [CatalogController::class, 'filters'])->name('filters');

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
        Route::get('/checkout/success', [CheckoutController::class,'result'])
            ->name('checkout.success');

        Route::get('/checkout/cancel', [CheckoutController::class,'cancelled'])
            ->name('checkout.cancel');

        Route::post('/checkout/{order}/payment', [CheckoutController::class,'payment'])->middleware('throttle:20,1')->name('checkout.payment');
        Route::get('/checkout/{order}/status', [CheckoutController::class,'status'])->name('checkout.status');
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
