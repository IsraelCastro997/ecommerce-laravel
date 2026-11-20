<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Admin\ChangeProductStatusRequest;

class ProductController extends Controller
{


   public function index(Request $request)
   {
      $query = Product::query();

      // Total de productos sin filtros.
      $total = $query->count();

      // Filtros del DataTable.
      $search = mb_substr(
         (string) $request->input('search.value', ''),
         0,
         255
      );

      $query
         ->when(
            $search !== '',
            fn($q) =>
            $q->where(
               fn($q) =>
               $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
            )
         )
         ->when(
            $request->filled('category'),
            fn($q) =>
            $q->where(
               'primary_category_id',
               $request->input('category')
            )
         )
         ->when(
            $request->filled('status'),
            fn($q) =>
            $q->where(
               'status',
               $request->input('status')
            )
         );

      if ($request->input('stock_filter') === 'in') $query->whereHas('inventoryBalances',fn($q)=>$q->whereColumn('qty_on_hand','>','qty_reserved'));
      if ($request->input('stock_filter') === 'out') $query->whereDoesntHave('inventoryBalances',fn($q)=>$q->whereColumn('qty_on_hand','>','qty_reserved'));
      // Total después de aplicar filtros.
      $filtered = $query->count();

      // Configuración de DataTables.
      $columns = ['sku', 'name', null, null, null, 'status'];

      $column = $columns[(int) $request->input('order.0.column', 0)] ?? 'id';

      $length = max(
         1,
         min(100, $request->integer('length', 10))
      );

      $page = intdiv(
         max(0, $request->integer('start', 0)),
         $length
      ) + 1;

      // Obtener productos de la página.
      $rows = $query
         ->with([
            'primaryCategory',
            'priceSnapshots' => fn($q) =>
            $q->orderByDesc('price_publication_id'),
         ])
         ->withSum(
            'inventoryBalances as on_hand',
            'qty_on_hand'
         )
         ->withSum(
            'inventoryBalances as reserved',
            'qty_reserved'
         )
         ->orderBy(
            $column,
            $request->input('order.0.dir') === 'desc'
               ? 'desc'
               : 'asc'
         )
         ->orderBy('id')
         ->paginate($length, ['*'], 'page', $page);

      return response()->json([
         'draw' => max(0, $request->integer('draw')),
         'recordsTotal' => $total,
         'recordsFiltered' => $filtered,
         'data' => $rows->getCollection()->map(fn($p) => [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'category' => $p->primaryCategory?->name
               ?? 'Sin categoría',
            'price' => $p->priceSnapshots->first()?->price_mxn,
            'stock' => max(
               0,
               ($p->on_hand ?? 0) - ($p->reserved ?? 0)
            ),
            'status' => $p->status,
         ]),
      ]);
   }


   public function store(SaveProductRequest $request)
   {
      $product = $this->saveProduct($request);
      if ($request->expectsJson()) return response()->json(['message' => 'Producto creado.', 'update_url' => route('admin.products.update', $product), 'data' => $product], 201);
      return redirect()->route('admin.products.index')->with('status', 'Producto creado.');
   }

   public function update(SaveProductRequest $request, Product $product)
   {
      $product = $this->saveProduct($request, $product);
      if ($request->expectsJson()) return response()->json(['message' => 'Producto actualizado.', 'update_url' => route('admin.products.update', $product), 'data' => $product]);
      return redirect()->route('admin.products.edit', $product)->with('status', 'Producto actualizado.');
   }

   public function destroy(Request $request, Product $product)
   {
      $product->delete();
      if ($request->expectsJson()) return response()->json(['message'=>'Producto eliminado.']);
      return redirect()->route('admin.products.index')->with('status', 'Producto eliminado del catálogo.');
   }

   public function status(ChangeProductStatusRequest $request, Product $product)
   {
      $product->update($request->validated());
      return response()->json(['data'=>['id'=>$product->id,'status'=>$product->status]]);
   }

   private function saveProduct(SaveProductRequest $request, ?Product $product = null): Product
   {
      return DB::transaction(function () use ($request, $product) {
         $data=$request->validated();
         $values=collect($data)->except(['price_mxn','price_usd','stock','historical_cost_mxn'])->all();
         $values['description']=$data['long_description'];
         if ($product) {
            $product=Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $product->update($values);
         } else $product=Product::create($values);

         $market=DB::table('markets')->where('code','MX')->lockForUpdate()->first();
         if (!$market) {
            $id=DB::table('markets')->insertGetId(['code'=>'MX','name'=>'México','primary_currency'=>'MXN']);
            $market=DB::table('markets')->where('id',$id)->first();
         }
         $publication=DB::table('price_publications')->where('market_id',$market->id)->where('is_active',true)->where('status','published')->lockForUpdate()->latest('id')->first();
         if (!$publication) {
            $id=DB::table('price_publications')->insertGetId(['market_id'=>$market->id,'status'=>'published','is_active'=>true,'published_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
            $publication=DB::table('price_publications')->where('id',$id)->first();
         }
         DB::table('product_price_snapshots')->updateOrInsert(['product_id'=>$product->id,'price_publication_id'=>$publication->id],['price_mxn'=>$data['price_mxn'],'price_usd'=>$data['price_usd'],'historical_cost_mxn'=>$data['historical_cost_mxn'],'calculated_at'=>now()]);

         $balances=DB::table('inventory_balances')->where('product_id',$product->id)->orderBy('id')->lockForUpdate()->get();
         $reserved=(int)$balances->sum('qty_reserved');
         // Stock means total on hand; reservations must remain valid.
         if ((int)$data['stock']<$reserved) throw ValidationException::withMessages(['stock'=>'No puede ser menor que las unidades reservadas ('.$reserved.').']);
         if ($balances->isEmpty()) {
            $warehouse=DB::table('warehouses')->orderBy('id')->value('id');
            if (!$warehouse) $warehouse=DB::table('warehouses')->insertGetId(['code'=>'MAIN','name'=>'Almacén principal','created_at'=>now(),'updated_at'=>now()]);
            DB::table('inventory_balances')->insert(['product_id'=>$product->id,'warehouse_id'=>$warehouse,'qty_on_hand'=>(int)$data['stock'],'qty_reserved'=>0,'created_at'=>now(),'updated_at'=>now()]);
         } else {
            $delta=(int)$data['stock']-(int)$balances->sum('qty_on_hand');
            foreach ($balances as $balance) {
               if ($delta===0) break;
               $adjustment=$delta>0 ? $delta : -min(-$delta,(int)$balance->qty_on_hand-(int)$balance->qty_reserved);
               $delta-=$adjustment;
               DB::table('inventory_balances')->where('id',$balance->id)->update(['qty_on_hand'=>(int)$balance->qty_on_hand+$adjustment,'version'=>DB::raw('version+1'),'updated_at'=>now()]);
            }
         }
         return $product;
      },3);
   }
}
