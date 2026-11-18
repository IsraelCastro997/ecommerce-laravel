<?php
namespace Tests\Feature\Presentation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
final class BladePagesTest extends TestCase {
 use RefreshDatabase;
 public static function publicPages(): array { return array_map(fn($url)=>[$url], ['/', '/catalogo', '/productos/MO-035', '/productos/desconocido', '/carrito', '/login', '/register', '/forgot-password', '/reset-password/example?email=demo@example.com']); }
 #[DataProvider('publicPages')]
 public function test_public_pages_render_blade_without_inertia(string $url): void {
  $this->get($url)->assertOk()->assertSee('Refugio Outdoor')->assertDontSee('data-page=', false)->assertDontSee('@inertia', false);
 }
 public static function accountPages(): array { return array_map(fn($url)=>[$url], ['/checkout','/pedido-confirmado/ejemplo','/checkout/success','/checkout/cancel','/dashboard','/cuenta','/cuenta/pedidos','/cuenta/pedidos/RF-1042','/cuenta/direcciones','/cuenta/moneda','/settings/profile','/settings/password','/settings/appearance','/confirm-password']); }
 #[DataProvider('accountPages')]
 public function test_account_pages_require_a_session_and_render(string $url): void {
  $this->get($url)->assertRedirect('/login');
  $this->actingAs(User::factory()->create())->get($url)->assertOk()->assertDontSee('data-page=', false);
 }
 public function test_all_admin_components_render_without_weakening_production_authorization(): void {
  $this->get('/admin/products')->assertRedirect('/login');
  $user=User::factory()->create();
  $this->actingAs($user)->get('/admin/products')->assertForbidden();
  // Presentation-only fixture: this gate exists solely inside this test.
  Gate::define('access-admin', fn(User $actor)=>$actor->id === $user->id);
  foreach (app('router')->getRoutes() as $route) {
   if (!str_starts_with($route->getName() ?? '', 'admin.')) continue;
   $url=preg_replace('/\{[^}]+\}/','example','/'.$route->uri());
   $this->get($url)->assertOk()->assertSee('demostración')->assertDontSee('data-page=', false);
  }
 }
 public function test_no_page_claims_a_payment_was_confirmed(): void {
  $this->actingAs(User::factory()->create());
  $this->get('/pedido-confirmado/123')->assertOk()->assertSee('Sin confirmación del servidor');
  $this->get('/checkout')->assertOk()->assertSee('Pago deshabilitado');
 }

 public function test_checkout_post_is_an_explicit_transport_stub_not_a_fake_purchase(): void {
  $this->actingAs(User::factory()->create());
  $this->postJson('/checkout', ['email'=>'sample@example.invalid', 'currency'=>'MXN', 'items'=>[['product_id'=>42,'quantity'=>2]]])->assertStatus(501)->assertJsonStructure(['message']);
  $this->assertDatabaseCount('orders', 0);
 }
 public function test_stripe_return_pages_do_not_trust_query_parameters_as_payment_confirmation(): void {
  $this->actingAs(User::factory()->create());
  $this->get('/checkout/success?session_id=forged&paid=1')->assertOk()->assertSee('Pendiente de verificación')->assertDontSee('Pago aprobado');
  $this->get('/checkout/cancel')->assertOk()->assertSee('Has salido de la pantalla de pago.')->assertDontSee('Pago rechazado');
 }
}
