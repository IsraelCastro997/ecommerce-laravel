<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class BackfillProductDetails extends Command
{
 protected $signature = 'catalog:backfill-details {--chunk=1000}';
 protected $description = 'Completa descripciones y precios históricos faltantes sin sobrescribir valores existentes';
 public function handle(): int
 {
  $count=0;
  DB::table('products')->select('id')->orderBy('id')->chunkById(max(1,min(5000,(int)$this->option('chunk'))), function($rows) use (&$count) {
   $count+=DB::table('products')->whereIn('id',$rows->pluck('id'))
    ->where(fn($q)=>$q->whereNull('short_description')->orWhereNull('long_description'))
    ->update(['short_description'=>DB::raw('COALESCE(short_description, LEFT(COALESCE(description, name), 500))'),
     'long_description'=>DB::raw('COALESCE(long_description, description, name)')]);
  });
  // Only use the price publication saved on the order, never today's catalog price.
  $items=0;
  DB::table('order_items')->select('id')->orderBy('id')->chunkById(1000,function($rows)use(&$items){
   $ids=$rows->pluck('id')->all();$marks=implode(',',array_fill(0,count($ids),'?'));
   $items+=DB::affectingStatement("UPDATE order_items i JOIN orders o ON o.id=i.order_id
    LEFT JOIN product_price_snapshots s ON s.product_id=i.product_id AND s.price_publication_id=o.price_publication_id
    SET i.price_mxn=COALESCE(i.price_mxn,CASE WHEN o.currency='MXN' THEN i.unit_price
      WHEN o.currency='USD' AND o.exchange_rate_value>0 THEN ROUND(i.unit_price*o.exchange_rate_value,2)
      WHEN o.currency='USD' AND s.price_usd=i.unit_price THEN s.price_mxn END),
    i.price_usd=COALESCE(i.price_usd,CASE WHEN o.currency='USD' THEN i.unit_price
      WHEN o.currency='MXN' AND s.price_mxn=i.unit_price THEN s.price_usd
      WHEN o.currency='MXN' AND o.exchange_rate_value>0 THEN ROUND(i.unit_price/o.exchange_rate_value,2) END)
    WHERE i.id IN ($marks) AND (i.price_mxn IS NULL OR i.price_usd IS NULL)",$ids);
  });
  $missing=DB::table('order_items')->whereNull('price_mxn')->orWhereNull('price_usd')->count();
  $this->info("Productos completados: {$count}. Detalles actualizados: {$items}. Detalles sin datos históricos suficientes: {$missing}.");
  return self::SUCCESS;
 }
}
