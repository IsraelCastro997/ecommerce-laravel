<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Product,Order,OrderItem,ProductPriceSnapshot,ProductImage};
use App\Services\Catalog\CatalogQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
class ProductDetailsBackfillTest extends TestCase {
 use RefreshDatabase;
 public function test_backfill_preserves_existing_text_and_historical_prices(): void {
  $p=Product::create(['sku'=>'TEST','slug'=>'test','name'=>'Producto','description'=>'Descripción anterior','short_description'=>'Texto manual']);
  $order=Order::forceCreate(['order_number'=>'TEST','idempotency_key'=>(string)Str::uuid(),'currency'=>'MXN','status'=>'pending_payment','subtotal'=>100,'grand_total'=>116,'exchange_rate_value'=>20]);
  $item=OrderItem::forceCreate(['order_id'=>$order->id,'product_id'=>$p->id,'quantity'=>1,'unit_price'=>100,'sku_snapshot'=>'TEST','name_snapshot'=>'Producto','line_total'=>116]);
  $this->artisan('catalog:backfill-details')->assertSuccessful();
  $this->assertSame('Texto manual',$p->fresh()->short_description);
  $this->assertSame('Descripción anterior',$p->fresh()->long_description);
  $this->assertSame('100.00',$item->fresh()->price_mxn);$this->assertSame('5.00',$item->fresh()->price_usd);
  $item->price_usd='7.00';$item->save();$this->artisan('catalog:backfill-details')->assertSuccessful();
  $this->assertSame('7.00',$item->fresh()->price_usd);
 }
 public function test_public_dto_exposes_descriptions_stock_prices_and_existing_demo_url(): void {
  $p=new Product(['name'=>'Equipo','short_description'=>'Resumen','long_description'=>'Detalle','available_until'=>'2026-12-01']);
  $p->id=42;$p->stock_on_hand=5;$p->stock_reserved=2;
  $price=new ProductPriceSnapshot();$price->price_mxn=100;$price->price_usd=5;
  $image=new ProductImage(['path'=>'https://example.test/equipo.jpg','is_primary'=>true]);
  $p->setRelation('priceSnapshots',collect([$price]));$p->setRelation('images',collect([$image]));$p->setRelation('primaryCategory',null);$p->setRelation('brand',null);
  $dto=app(CatalogQueryService::class)->toDto($p);
  $this->assertSame('Resumen',$dto['short_description']);$this->assertSame('Detalle',$dto['detail']);
  $this->assertSame(3,$dto['available_quantity']);$this->assertEquals(5,$dto['prices']['USD']);
  $this->assertSame('https://picsum.photos/seed/product-42/640/640',$dto['image_url']);
 }
}
