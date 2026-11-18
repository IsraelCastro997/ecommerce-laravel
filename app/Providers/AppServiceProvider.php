<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use App\Payments\StripeWebhookHandler;
use Illuminate\Support\Facades\Event;
use Laravel\Cashier\Events\WebhookReceived;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            WebhookReceived::class,
            [StripeWebhookHandler::class, 'handle'],
        );   
    }
}
