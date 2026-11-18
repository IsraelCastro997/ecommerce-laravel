<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{User,Order};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
class CheckoutFlowTest extends TestCase {
 use RefreshDatabase;
 private function product(): int {
  app(PermissionRegistrar::class)->forgetCachedPermissions();
  $admin=User::factory()->create();$admin->givePermissionTo(Permission::findOrCreate('access-admin','web'));$this->actingAs($admin);
  $id=$this->postJson('/admin/products',['sku'=>'CHECKOUT','slug'=>'checkout','name'=>'Equipo','short_description'=>'Resumen','long_description'=>'Detalle','status'=>'active','price_mxn'=>100,'price_usd'=>5,'stock'=>5,'image_url'=>'https://example.test/image.jpg','available_until'=>'2027-12-01'])->assertCreated()->json('data.id');
  $this->actingAs(User::factory()->create());return $id;
 }
 public function test_checkout_creates_pending_order_with_both_prices_and_retries_safely(): void {
  $id=$this->product();
  $this->get('/checkout')->assertOk()->assertSee('Continuar con mis datos')->assertSee('Generar pre-pedido');
  $payload=['email'=>'buyer@example.test','currency'=>'MXN','items'=>[['product_id'=>$id,'quantity'=>2]],'idempotency_key'=>(string)Str::uuid(),'grand_total'=>0.01];
  $this->postJson('/checkout',$payload)->assertCreated()->assertJsonPath('data.status','pending_payment')->assertJsonPath('data.subtotal','200.00')->assertJsonPath('data.tax_total','32.00')->assertJsonPath('data.grand_total','232.00')->assertJsonPath('data.items.0.price_mxn','100.00')->assertJsonPath('data.items.0.price_usd','5.00');
  $this->postJson('/checkout',$payload)->assertOk();$this->assertSame(1,Order::count());
 }
 public function test_stock_errors_return_json_without_creating_order(): void {
  $id=$this->product();
  $this->postJson('/checkout',['email'=>'buyer@example.test','currency'=>'MXN','items'=>[['product_id'=>$id,'quantity'=>6]],'idempotency_key'=>(string)Str::uuid()])->assertUnprocessable();
  $this->assertSame(0,Order::count());
 }
}
