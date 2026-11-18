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
use App\Http\Controllers\Admin\DashboardController;

Route::middleware(['auth', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::livewire('/', Dashboard::class)
            ->name('dashboard');

        Route::get('/products/data', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.data');
        Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
        Route::patch('/products/{product}/status', [\App\Http\Controllers\Admin\ProductController::class, 'status'])->name('products.status');
        Route::put('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');
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
        Route::livewire('/products/create', \App\Livewire\Admin\Products\ProductCreate::class)->name('products.create');
        Route::livewire('/products/{product}/edit', \App\Livewire\Admin\Products\ProductEdit::class)->name('products.edit');
        Route::livewire('/products/{product}', \App\Livewire\Admin\Products\ProductDetail::class)->name('products.show');
        Route::livewire('/attributes', \App\Livewire\Admin\Attributes\AttributeManager::class)->name('attributes.index');
        Route::livewire('/inventory', \App\Livewire\Admin\Inventory\InventoryManager::class)->name('inventory.index');
        Route::livewire('/inventory/lots', \App\Livewire\Admin\Inventory\InventoryLots::class)->name('inventory.lots');
        Route::livewire('/inventory/movements', \App\Livewire\Admin\Inventory\InventoryMovements::class)->name('inventory.movements');
        Route::livewire('/warehouses', \App\Livewire\Admin\Inventory\WarehouseManager::class)->name('warehouses.index');
        Route::livewire('/pricing', \App\Livewire\Admin\Pricing\PriceManager::class)->name('pricing.index');
        Route::livewire('/pricing/history', \App\Livewire\Admin\Pricing\PriceHistory::class)->name('pricing.history');
        Route::livewire('/pricing/policies', \App\Livewire\Admin\Pricing\PricingPolicyManager::class)->name('pricing.policies');
        Route::livewire('/orders/{order}', \App\Livewire\Admin\Orders\OrderDetail::class)->name('orders.show');
        Route::livewire('/payments', \App\Livewire\Admin\Payments\PaymentManager::class)->name('payments.index');
        Route::livewire('/payments/{payment}', \App\Livewire\Admin\Payments\PaymentDetail::class)->name('payments.show');
        Route::livewire('/customers', \App\Livewire\Admin\Customers\CustomerManager::class)->name('customers.index');
        Route::livewire('/customers/{customer}', \App\Livewire\Admin\Customers\CustomerDetail::class)->name('customers.show');
        Route::livewire('/roles', \App\Livewire\Admin\Access\RoleManager::class)->name('roles.index');
        Route::livewire('/permissions', \App\Livewire\Admin\Access\PermissionManager::class)->name('permissions.index');
        Route::livewire('/reports/inventory', \App\Livewire\Admin\Reports\InventoryReport::class)->name('reports.inventory');
        Route::livewire('/exchange-rates/history', \App\Livewire\Admin\Currency\ExchangeRateHistory::class)->name('exchange-rates.history');
        Route::livewire('/integrations', \App\Livewire\Admin\Integrations\IntegrationStatus::class)->name('integrations.index');
        Route::livewire('/integrations/search', \App\Livewire\Admin\Integrations\SearchIndexStatus::class)->name('integrations.search');
        Route::livewire('/settings', \App\Livewire\Admin\Settings\StoreSettings::class)->name('settings.index');
        Route::livewire('/suppliers', \App\Livewire\Admin\Suppliers\SupplierManager::class)->name('suppliers.index');
        Route::livewire('/suppliers/{supplier}', \App\Livewire\Admin\Suppliers\SupplierDetail::class)->name('suppliers.show');
    
        Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
        Route::get('/dashboard/summary',[DashboardController::class,'summary'])->name('dashboard.summary');
        Route::get('/dashboard/sales',[DashboardController::class,'sales'])->name('dashboard.sales');
        Route::get('/dashboard/top-products',[DashboardController::class, 'topProducts'])->name('dashboard.top-products');
        });
