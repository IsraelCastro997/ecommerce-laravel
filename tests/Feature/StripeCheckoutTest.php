<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{User,Order,OrderItem,Product};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
class StripeCheckoutTest extends TestCase {
 use RefreshDatabase;
 private array $session=[];
 private array $requests=[];
 protected function tearDown(): void { ApiRequestor::setHttpClient(null); parent::tearDown(); }
 private function order(): Order {
  config(['cashier.secret'=>'sk_test_local_fake','cashier.webhook.secret'=>'whsec_local_test']);
  $user=User::factory()->create();$this->actingAs($user);
  $product=Product::create(['name'=>'Equipo','sku'=>'TEST','slug'=>'test']);
  $order=Order::forceCreate(['user_id'=>$user->id,'order_number'=>'RF-TEST','customer_email'=>$user->email,'idempotency_key'=>(string)Str::uuid(),'status'=>'pending_payment','currency'=>'MXN','subtotal'=>200,'tax_total'=>32,'grand_total'=>232]);
  $item=OrderItem::forceCreate(['order_id'=>$order->id,'product_id'=>$product->id,'sku_snapshot'=>'TEST','name_snapshot'=>'Equipo','quantity'=>2,'unit_price'=>100,'line_total'=>232]);
  $warehouse=DB::table('warehouses')->insertGetId(['code'=>'MAIN','name'=>'Principal']);
  DB::table('inventory_balances')->insert(['warehouse_id'=>$warehouse,'product_id'=>$product->id,'qty_on_hand'=>5,'qty_reserved'=>2]);
  DB::table('stock_reservations')->insert(['operation_id'=>(string)Str::uuid(),'order_item_id'=>$item->id,'product_id'=>$product->id,'warehouse_id'=>$warehouse,'quantity'=>2,'status'=>'held','expires_at'=>now()->addDay()]);
  $this->session=['id'=>'cs_test_local','object'=>'checkout.session','status'=>'open','payment_status'=>'unpaid','url'=>'https://checkout.stripe.com/c/pay/test','currency'=>'mxn','amount_total'=>23200,'client_reference_id'=>(string)$order->id,'metadata'=>['order_id'=>(string)$order->id],'payment_intent'=>'pi_test_local'];
  $client=\Mockery::mock(ClientInterface::class);
  $client->shouldReceive('request')->andReturnUsing(function($method,$url,$headers,$params){
   $this->requests[]=compact('method','url','headers','params');
   return [json_encode($this->session),200,[]];
  });
  ApiRequestor::setHttpClient($client);
  return $order;
 }
 private function webhook(string $type='checkout.session.completed',string $id='evt_test_1',bool $valid=true) {
  $body=json_encode(['id'=>$id,'object'=>'event','type'=>$type,'data'=>['object'=>$this->session]]);
  $time=time();$signature=hash_hmac('sha256',$time.'.'.$body,$valid?'whsec_local_test':'wrong');
  return $this->call('POST','/stripe/webhook',[],[],[],['CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json','HTTP_STRIPE_SIGNATURE'=>"t={$time},v1={$signature}"],$body);
 }
 public function test_payment_uses_server_total_and_reuses_session(): void {
  $order=$this->order();
  $this->postJson('/checkout/'.$order->id.'/payment',['total'=>1])->assertOk()->assertJsonPath('checkout_url','https://checkout.stripe.com/c/pay/test');
  $this->postJson('/checkout/'.$order->id.'/payment')->assertOk();
  $posts=array_values(array_filter($this->requests,fn($r)=>$r['method']==='post'));
  $this->assertCount(1,$posts);$this->assertSame(23200,$posts[0]['params']['line_items'][0]['price_data']['unit_amount']);
  $this->assertSame('mxn',$posts[0]['params']['line_items'][0]['price_data']['currency']);
  $this->assertSame(1,DB::table('payment_transactions')->count());
 }
 public function test_signed_paid_webhook_consumes_stock_exactly_once(): void {
  $order=$this->order();$this->postJson('/checkout/'.$order->id.'/payment')->assertOk();
  $this->session['status']='complete';$this->session['payment_status']='paid';
  $this->webhook()->assertOk();$this->webhook()->assertOk();$this->webhook('checkout.session.completed','evt_test_2')->assertOk();
  $this->assertSame('paid',$order->fresh()->status);$this->assertNotNull($order->fresh()->paid_at);
  $this->assertDatabaseHas('inventory_balances',['qty_on_hand'=>3,'qty_reserved'=>0]);
  $this->assertDatabaseHas('payment_transactions',['status'=>'paid','stripe_payment_intent_id'=>'pi_test_local']);
  $this->postJson('/checkout/'.$order->id.'/payment')->assertConflict();
 }
 public function test_invalid_signature_and_missing_configuration_fail_closed(): void {
  $order=$this->order();$this->postJson('/checkout/'.$order->id.'/payment')->assertOk();
  $this->webhook(valid:false)->assertForbidden();$this->assertSame('pending_payment',$order->fresh()->status);
  config(['cashier.webhook.secret'=>null]);$this->webhook()->assertStatus(503);
  $this->postJson('/checkout/'.$order->id.'/payment')->assertStatus(503);
 }
 public function test_expired_session_releases_reservations_without_selling_stock(): void {
  $order=$this->order();$this->postJson('/checkout/'.$order->id.'/payment')->assertOk();
  $this->session['status']='expired';$this->webhook('checkout.session.expired')->assertOk();
  $this->assertSame('expired',$order->fresh()->status);$this->assertDatabaseHas('inventory_balances',['qty_on_hand'=>5,'qty_reserved'=>0]);
 }
 public function test_return_page_never_marks_paid_and_other_users_cannot_access_order(): void {
  $order=$this->order();$this->postJson('/checkout/'.$order->id.'/payment')->assertOk();
  $this->get('/checkout/success?session_id=cs_test_local')->assertOk()->assertSee('Pendiente de pago');
  $this->get('/checkout/cancel?order='.$order->id)->assertOk();
  $this->assertNull($order->fresh()->paid_at);
  $this->actingAs(User::factory()->create());
  $this->postJson('/checkout/'.$order->id.'/payment')->assertForbidden();
  $this->getJson('/checkout/'.$order->id.'/status')->assertForbidden();
  $this->get('/checkout/success?session_id=cs_test_local')->assertNotFound();
  $this->get('/pedido-confirmado/'.$order->id)->assertForbidden();
 }
 public function test_inconsistent_paid_amount_does_not_fulfill(): void {
  $order=$this->order();$this->postJson('/checkout/'.$order->id.'/payment')->assertOk();
  $this->session['payment_status']='paid';$this->session['status']='complete';$this->session['amount_total']=1;
  $this->webhook()->assertServerError();$this->assertNull($order->fresh()->paid_at);
  $this->assertDatabaseHas('inventory_balances',['qty_on_hand'=>5,'qty_reserved'=>2]);
 }
}
