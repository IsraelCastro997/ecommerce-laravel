<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class BackfillOrderHistory extends Command {
 protected $signature='orders:backfill-history {--chunk=500 : Pedidos por transacción} {--dry-run : Mostrar faltantes sin escribir}';
 protected $description='Reconstruye costos, precios y tipo de cambio históricos faltantes sin crear pedidos ni sobrescribir datos conocidos';
 public function handle(): int {
  if(!in_array(DB::getDriverName(),['mysql','mariadb'],true)){$this->error('Este comando requiere MySQL o MariaDB.');return self::FAILURE;}
  $costs=$rates=$prices=0;
  if(!$this->option('dry-run')) DB::table('orders')->select('id')->orderBy('id')->chunkById(max(1,min(5000,(int)$this->option('chunk'))),function($orders)use(&$costs,&$rates,&$prices){
   $ids=$orders->pluck('id')->all();$marks=implode(',',array_fill(0,count($ids),'?'));
   DB::transaction(function()use($ids,$marks,&$costs,&$rates,&$prices){
    // A referenced historical rate is valid evidence; today's rate is not.
    $rates+=DB::affectingStatement("UPDATE orders o
     LEFT JOIN price_publications p ON p.id=o.price_publication_id
     JOIN exchange_rates e ON e.id=COALESCE(o.exchange_rate_id,p.exchange_rate_id)
     SET o.exchange_rate_value=CASE WHEN e.base_currency='USD' AND e.quote_currency='MXN' THEN e.rate ELSE ROUND(1/e.rate,8) END,
         o.exchange_rate_id=COALESCE(o.exchange_rate_id,e.id)
     WHERE o.id IN ($marks) AND (o.exchange_rate_value IS NULL OR o.exchange_rate_value<=0)
       AND e.rate>0 AND e.effective_date<=DATE(o.created_at)
       AND ((e.base_currency='USD' AND e.quote_currency='MXN') OR (e.base_currency='MXN' AND e.quote_currency='USD'))",$ids);
    // The line cost is total MXN (unit cost multiplied by quantity).
    $costs+=DB::affectingStatement("UPDATE order_items i JOIN orders o ON o.id=i.order_id
     JOIN product_price_snapshots s ON s.product_id=i.product_id AND s.price_publication_id=o.price_publication_id
     SET i.allocated_cost_mxn=ROUND(s.historical_cost_mxn*i.quantity,4)
     WHERE o.id IN ($marks) AND i.allocated_cost_mxn IS NULL
       AND s.historical_cost_mxn IS NOT NULL AND s.historical_cost_mxn>=0 AND s.calculated_at<=o.created_at",$ids);
    $prices+=DB::affectingStatement("UPDATE order_items i JOIN orders o ON o.id=i.order_id
     LEFT JOIN product_price_snapshots s ON s.product_id=i.product_id AND s.price_publication_id=o.price_publication_id AND s.calculated_at<=o.created_at
     SET i.price_mxn=COALESCE(i.price_mxn,CASE WHEN o.currency='MXN' THEN i.unit_price
      WHEN o.currency='USD' AND s.price_usd=i.unit_price THEN s.price_mxn
      WHEN o.currency='USD' AND o.exchange_rate_value>0 THEN ROUND(i.unit_price*o.exchange_rate_value,2) END),
     i.price_usd=COALESCE(i.price_usd,CASE WHEN o.currency='USD' THEN i.unit_price
      WHEN o.currency='MXN' AND s.price_mxn=i.unit_price THEN s.price_usd
      WHEN o.currency='MXN' AND o.exchange_rate_value>0 THEN ROUND(i.unit_price/o.exchange_rate_value,2) END)
     WHERE o.id IN ($marks) AND (i.price_mxn IS NULL OR i.price_usd IS NULL)",$ids);
   },3);
  });
  $this->info("Costos completados: {$costs}. Tipos de cambio completados: {$rates}. Detalles con precios completados: {$prices}.");
  $missingCosts=DB::table('order_items')->whereNull('allocated_cost_mxn')->count();
  $missingRates=DB::table('orders')->where('currency','USD')->where(fn($q)=>$q->whereNull('exchange_rate_value')->orWhere('exchange_rate_value','<=',0))->count();
  $missingPrices=DB::table('order_items')->where(fn($q)=>$q->whereNull('price_mxn')->orWhereNull('price_usd'))->count();
  $this->table(['Dato histórico','Pendientes'],[['Costos de detalles',$missingCosts],['Tipos de cambio para pedidos USD',$missingRates],['Precios de detalles',$missingPrices]]);
  if($missingCosts || $missingRates || $missingPrices)$this->warn('Los faltantes requieren evidencia histórica. No se sustituyen con costos, precios ni cotizaciones actuales.');
  return self::SUCCESS;
 }
}
