<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
class AdminProductCrudTest extends TestCase
{
 use RefreshDatabase;
 private function admin(): User {
  app(PermissionRegistrar::class)->forgetCachedPermissions();
  $user=User::factory()->create();
  $user->givePermissionTo(Permission::findOrCreate('access-admin','web'));
  return $user;
 }
 private function data(): array {return ['sku'=>'TEST-CRUD','slug'=>'test-crud','name'=>'Mochila de prueba','status'=>'draft','origin_country'=>'MX','price_mxn'=>'100.00','price_usd'=>'5.00','stock'=>10,'image_url'=>'https://example.test/product.jpg','short_description'=>'Descripcion breve','long_description'=>'Descripcion completa','available_until'=>'2027-12-01'];}
 public function test_create_edit_list_and_soft_delete(): void {
  $this->actingAs($this->admin());
  $this->get('/admin/products')->assertOk()->assertSee('Precio USD');
  $this->get('/admin/products/create')->assertOk()->assertSee('Guardar producto');
  $this->post('/admin/products',$this->data()+['version'=>999])->assertSessionHasNoErrors()->assertRedirect();
  $product=Product::where('sku','TEST-CRUD')->firstOrFail();
  $this->assertEquals(1,$product->version);
  $this->get('/admin/products/'.$product->id.'/edit')->assertOk()->assertSee('Mochila de prueba');
  $this->get('/admin/products/'.$product->id)->assertOk()->assertSee('Mochila de prueba');
  $this->put('/admin/products/'.$product->id,array_replace($this->data(),['name'=>'Mochila actualizada']))->assertSessionHasNoErrors()->assertRedirect();
  $this->assertEquals('Mochila actualizada',$product->fresh()->name);
  $this->getJson('/admin/products/data?length=10&search[value]=actualizada')->assertOk()->assertJsonPath('recordsFiltered',1)->assertJsonPath('data.0.id',$product->id);
  $this->delete('/admin/products/'.$product->id)->assertRedirect('/admin/products');
  $this->assertSoftDeleted('products',['id'=>$product->id]);
  $this->getJson('/admin/products/data')->assertJsonPath('recordsTotal',0);
  $this->get('/admin/products/'.$product->id.'/edit')->assertNotFound();
 }
 public function test_validation_and_customer_access(): void {
  $this->actingAs($this->admin());
  $this->post('/admin/products',['name'=>''])->assertSessionHasErrors(['sku','name','slug','status']);
  $this->post('/admin/products',$this->data())->assertSessionHasNoErrors();
  $this->post('/admin/products',$this->data())->assertSessionHasErrors(['sku','slug']);
  $this->actingAs(User::factory()->create());
  $product=Product::firstOrFail();
  $this->post('/admin/products',$this->data())->assertForbidden();
  $this->put('/admin/products/'.$product->id,$this->data())->assertForbidden();
  $this->delete('/admin/products/'.$product->id)->assertForbidden();
  $this->getJson('/admin/products/data')->assertForbidden();
 }
 public function test_server_pagination_and_filters(): void {
  $this->actingAs($this->admin());
  for($i=1;$i<=12;$i++) Product::create(array_replace($this->data(),['sku'=>'SKU-'.$i,'slug'=>'item-'.$i,'status'=>$i<=10?'draft':'active']));
  $this->getJson('/admin/products/data?length=10&start=10')->assertOk()->assertJsonCount(2,'data')->assertJsonPath('recordsTotal',12);
  $this->getJson('/admin/products/data?status=active')->assertJsonCount(2,'data')->assertJsonPath('recordsFiltered',2);
 }
 public function test_json_form_returns_inline_validation_and_saved_product_without_redirect(): void {
  $this->actingAs($this->admin());
  $this->postJson('/admin/products',['name'=>''])->assertUnprocessable()->assertJsonValidationErrors(['sku','slug','status','name']);
  $response=$this->postJson('/admin/products',$this->data())->assertCreated()->assertJsonPath('message','Producto creado.');
  $id=$response->json('data.id');
  $this->assertSame('Descripcion breve',$response->json('data.short_description'));
  $this->assertSame('Descripcion completa',$response->json('data.long_description'));
  $this->assertSame('Descripcion completa',$response->json('data.description'));
  $this->assertSame(route('admin.products.update',$id),$response->json('update_url'));
  $this->putJson('/admin/products/'.$id,array_replace($this->data(),['name'=>'Nombre actualizado']))->assertOk()->assertJsonPath('data.name','Nombre actualizado');
  $this->assertEquals(1,Product::count());
  $this->postJson('/admin/products',$this->data())->assertUnprocessable()->assertJsonValidationErrors(['sku','slug']);
 }

 public function test_required_fields_prices_name_and_switch_are_enforced(): void {
  $this->actingAs($this->admin());
  $this->postJson('/admin/products',array_replace($this->data(),['name'=>'Móchila!','price_mxn'=>0,'price_usd'=>-2,'short_description'=>'','long_description'=>'','image_url'=>'not-url','available_until'=>'']))
   ->assertUnprocessable()->assertJsonValidationErrors(['name','price_mxn','price_usd','short_description','long_description','image_url','available_until']);
  $created=$this->postJson('/admin/products',$this->data())->assertCreated();
  $id=$created->json('data.id');
  $this->assertDatabaseHas('product_price_snapshots',['product_id'=>$id,'price_usd'=>'5.00','price_mxn'=>'100.00']);
  $this->assertDatabaseHas('inventory_balances',['product_id'=>$id,'qty_on_hand'=>10]);
  $this->patchJson('/admin/products/'.$id.'/status',['status'=>'active'])->assertOk()->assertJsonPath('data.status','active');
  $this->patchJson('/admin/products/'.$id.'/status',['status'=>'inactive'])->assertOk()->assertJsonPath('data.status','inactive');
  $this->patchJson('/admin/products/'.$id.'/status',['status'=>'unknown'])->assertUnprocessable();
  $this->actingAs(User::factory()->create());
  $this->patchJson('/admin/products/'.$id.'/status',['status'=>'active'])->assertForbidden();
 }
 public function test_stock_reservations_survive_edit_and_quick_filters_use_database(): void {
  $this->actingAs($this->admin());
  $id=$this->postJson('/admin/products',$this->data())->assertCreated()->json('data.id');
  \Illuminate\Support\Facades\DB::table('inventory_balances')->where('product_id',$id)->update(['qty_reserved'=>4]);
  $this->putJson('/admin/products/'.$id,array_replace($this->data(),['stock'=>3,'price_mxn'=>200]))->assertUnprocessable()->assertJsonValidationErrors('stock');
  $this->assertDatabaseHas('product_price_snapshots',['product_id'=>$id,'price_mxn'=>'100.00']);
  $this->putJson('/admin/products/'.$id,array_replace($this->data(),['stock'=>4]))->assertOk();
  $this->assertDatabaseHas('inventory_balances',['product_id'=>$id,'qty_reserved'=>4,'qty_on_hand'=>4]);
  $this->getJson('/admin/products/data?stock_filter=out')->assertJsonPath('recordsFiltered',1);
  $this->getJson('/admin/products/data?stock_filter=in')->assertJsonPath('recordsFiltered',0);
 }
}
