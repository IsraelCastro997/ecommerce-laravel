<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Products\ProductManager;
use App\Livewire\Admin\Categories\CategoryManager;
use App\Livewire\Admin\Brands\BrandManager;
use App\Livewire\Admin\Orders\OrderManager;
use App\Livewire\Admin\Reports\SalesReport;
use App\Livewire\Admin\Reports\TopProductsReport;
use App\Livewire\Admin\Currency\ExchangeRateManager;

Route::middleware(['auth', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::livewire('/', Dashboard::class)
            ->name('dashboard');

        Route::livewire('/products', ProductManager::class)
            ->name('products.index');

        Route::livewire('/categories', CategoryManager::class)
            ->name('categories.index');

        Route::livewire('/brands', BrandManager::class)
            ->name('brands.index');

        Route::livewire('/orders', OrderManager::class)
            ->name('orders.index');

        Route::livewire('/reports/sales', SalesReport::class)
            ->name('reports.sales');

        Route::livewire('/reports/top-products', TopProductsReport::class)
            ->name('reports.top-products');

        Route::livewire('/exchange-rates', ExchangeRateManager::class)
            ->name('exchange-rates.index');
    });