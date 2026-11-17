<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Account\AccountDashboard;
use App\Livewire\Account\OrderHistory;
use App\Livewire\Account\OrderDetail;
use App\Livewire\Account\AddressManager;
use App\Livewire\Account\CurrencyPreferences;

Route::middleware(['auth'])
    ->prefix('cuenta')
    ->name('account.')
    ->group(function () {

        Route::livewire('/', AccountDashboard::class)
            ->name('index');

        Route::livewire('/pedidos', OrderHistory::class)
            ->name('orders.index');

        Route::livewire('/pedidos/{order}', OrderDetail::class)
            ->name('orders.show');

        Route::livewire('/direcciones', AddressManager::class)
            ->name('addresses');

        Route::livewire('/moneda', CurrencyPreferences::class)
            ->name('currency');
    });