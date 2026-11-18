<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureStripeWebhookConfigured {
 public function handle(Request $request, Closure $next) {
  if ($request->is(trim(config('cashier.path','stripe'),'/').'/webhook')) {
   abort_unless(config('cashier.webhook.secret'),503,'El webhook de Stripe no está configurado.');
  }
  return $next($request);
 }
}
