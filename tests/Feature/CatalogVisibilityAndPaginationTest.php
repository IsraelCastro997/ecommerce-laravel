<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Product,User};
use App\Livewire\Admin\Products\ProductManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
class CatalogVisibilityAndPaginationTest extends TestCase {
 use RefreshDatabase;
 private function publication(): int {
  $market=DB::table('markets')->insertGetId(['code'=>'MX','name'=>'México','primary_currency'=>'MXN']);
  return DB::table('price_publications')->insertGetId(['market_id'=>$market,'status'=>'published','is_active'=>true]);
 }
 private function product(int $publication,string $sku,array $attributes=[]): Product {
  $product=Product::create(array_replace(['sku'=>$sku,'slug'=>strtolower($sku),'name'=>$sku,'status'=>'active'],$attributes));
  DB::table('product_price_snapshots')->insert(['product_id'=>$product->id,'price_publication_id'=>$publication,'price_usd'=>'5.25','price_mxn'=>'100.99','calculated_at'=>now()]);
  return $product;
 }
 public function test_public_catalog_detail_and_lookup_reject_expired_future_inactive_and_deleted(): void {
  $publication=$this->publication();
  $valid=$this->product($publication,'VISIBLE',['available_from'=>now()->subDay(),'available_until'=>now()->addDay()]);
  $open=$this->product($publication,'OPEN');
  $expired=$this->product($publication,'EXPIRED',['available_until'=>now()->subSecond()]);
  $future=$this->product($publication,'FUTURE',['available_from'=>now()->addDay()]);
  $this->product($publication,'INACTIVE',['status'=>'inactive']);
  $deleted=$this->product($publication,'DELETED');$deleted->delete();
  $response=$this->getJson('/storefront/products')->assertOk()->assertJsonCount(2,'data');
  $this->assertEqualsCanonicalizing([$valid->id,$open->id],array_column($response->json('data'),'product_id'));
  $this->getJson('/storefront/products/by-id/'.$expired->id)->assertNotFound();
  $this->getJson('/storefront/products/by-id/'.$future->id)->assertNotFound();
  $this->postJson('/storefront/products/loockup',['product_ids'=>[$valid->id,$expired->id,$future->id,$deleted->id]])->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.product_id',$valid->id);
 }
 public function test_livewire_search_paginates_filters_and_preserves_actions_and_both_prices(): void {
  app(PermissionRegistrar::class)->forgetCachedPermissions();$admin=User::factory()->create();$admin->givePermissionTo(Permission::findOrCreate('access-admin','web'));$this->actingAs($admin);
  $publication=$this->publication();
  for($i=1;$i<=12;$i++)$this->product($publication,'SKU'.str_pad((string)$i,2,'0',STR_PAD_LEFT));
  $last=Product::where('sku','SKU12')->firstOrFail();
  $component=Livewire::test(ProductManager::class)->assertSee('Precio USD')->assertSee('Precio MXN')->assertSee('5.25 USD')->assertSee('100.99 MXN')
   ->assertViewHas('products',fn($p)=>$p->total()===12&&$p->count()===10)
   ->call('setPage',2)->assertViewHas('products',fn($p)=>$p->currentPage()===2&&$p->count()===2)
   ->set('search','SKU12')->assertViewHas('products',fn($p)=>$p->currentPage()===1&&$p->total()===1)
   ->call('setActive',$last->id,false)->assertSee('Producto desactivado.');
  $this->assertSame('inactive',$last->fresh()->status);
  $component->call('clearFilters')->call('quickStatus','inactive')->assertViewHas('products',fn($p)=>$p->total()===1)
   ->call('deleteProduct',$last->id)->assertViewHas('products',fn($p)=>$p->total()===0);
  $this->assertSoftDeleted('products',['id'=>$last->id]);
 }
 public function test_customer_cannot_invoke_admin_livewire_component(): void {
  $this->actingAs(User::factory()->create());Livewire::test(ProductManager::class)->assertForbidden();
 }
}
