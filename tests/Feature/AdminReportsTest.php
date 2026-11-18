<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Order,OrderItem,Product,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
class AdminReportsTest extends TestCase
{
 use RefreshDatabase;
 private function admin(): void {
  app(PermissionRegistrar::class)->forgetCachedPermissions();
  $user=User::factory()->create();$user->givePermissionTo(Permission::findOrCreate('access-admin','web'));$this->actingAs($user);
 }
 private function order(array $attributes=[],?string $cost='60.0000'): Order {
  $order=Order::forceCreate(array_replace(['order_number'=>'REPORT-'.Str::random(12),'idempotency_key'=>(string)Str::uuid(),
   'currency'=>'MXN','subtotal'=>'100.00','tax_total'=>'16.00','discount_total'=>'0.00','shipping_total'=>'0.00','grand_total'=>'116.00',
   'status'=>'paid','paid_at'=>'2026-11-15 12:00:00','created_at'=>'2026-11-15 12:00:00'], $attributes));
  $product=Product::create(['name'=>'Equipo','sku'=>Str::random(10),'slug'=>Str::lower(Str::random(10))]);
  OrderItem::forceCreate(['order_id'=>$order->id,'product_id'=>$product->id,'sku_snapshot'=>$product->sku,'name_snapshot'=>$product->name,
   'quantity'=>2,'unit_price'=>'50.00','line_total'=>'116.00','tax_total'=>'16.00','allocated_cost_mxn'=>$cost]);
  return $order;
 }
 public function test_order_range_includes_whole_days_and_preserves_pagination(): void {
  $this->admin();
  $this->order(['created_at'=>'2026-11-14 23:59:59']);
  $this->order(['created_at'=>'2026-11-16 00:00:00']);
  for($i=0;$i<11;$i++) $this->order(['created_at'=>'2026-11-15 23:59:59']);
  $url='/admin/dashboard/orders?from=2026-11-15&until=2026-11-15';
  $this->getJson($url)->assertOk()->assertJsonPath('total',11)->assertJsonCount(10,'data')->assertJsonPath('last_page',2);
  $this->getJson($url.'&page=2')->assertOk()->assertJsonCount(1,'data');
  $this->get('/admin/orders')->assertOk()->assertSee('fecha de creación');
 }
 public function test_profit_excludes_tax_shipping_discounts_and_uses_total_line_cost(): void {
  $this->admin();$this->order(['discount_total'=>'10.00','shipping_total'=>'20.00','grand_total'=>'124.40']);
  $this->order(['paid_at'=>null,'status'=>'pending_payment']);
  $this->getJson('/admin/dashboard/sales?from=2026-11-15&until=2026-11-15')
   ->assertOk()->assertJsonPath('data.0.profit','30.00')->assertJsonPath('data.0.total','124.40')->assertJsonPath('data.0.orders',1);
 }
 public function test_usd_cost_conversion_uses_saved_exchange_rate(): void {
  $this->admin();$this->order(['currency'=>'USD','exchange_rate_value'=>'20.00000000'],'600.0000');
  $this->getJson('/admin/dashboard/sales?from=2026-11-15&until=2026-11-15&currency=USD')
   ->assertOk()->assertJsonPath('data.0.profit','70.00');
 }
 public function test_missing_cost_or_rate_never_becomes_zero_cost_profit(): void {
  $this->admin();$this->order([],null);$this->order(['currency'=>'USD'], '60.0000');
  $data=$this->getJson('/admin/dashboard/sales?from=2026-11-15&until=2026-11-15')->assertOk()->json('data');
  foreach($data as $row){$this->assertNull($row['profit']);$this->assertSame(1,$row['orders_without_cost']);}
 }
 public function test_losses_zero_cost_and_validation(): void {
  $this->admin();$this->order([], '140.0000');
  $this->getJson('/admin/dashboard/sales?from=2026-11-15&until=2026-11-15')->assertJsonPath('data.0.profit','-40.00');
  foreach(['orders','sales','top-products'] as $endpoint) {
   $this->getJson('/admin/dashboard/'.$endpoint.'?from=2026-11-16&until=2026-11-15')->assertUnprocessable();
  }
  $this->getJson('/admin/dashboard/orders?until=2026-11-15')->assertOk();
  $this->get('/admin/reports/sales')->assertOk()->assertSee('Ganancia bruta');
 }
 public function test_reports_are_admin_only(): void {
  $this->actingAs(User::factory()->create());
  foreach(['orders','sales','top-products'] as $endpoint) $this->getJson('/admin/dashboard/'.$endpoint)->assertForbidden();
 }
}
