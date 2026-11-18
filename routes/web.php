<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Store\Home;
use App\Livewire\Account\AccountDashboard;
Route::livewire('/', Home::class)->name('home');
Route::livewire('/dashboard', AccountDashboard::class)->middleware(['auth', 'verified'])->name('dashboard');
require __DIR__.'/store.php';
require __DIR__.'/account.php';
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
