<?php
namespace App\Livewire\Admin\Products;
use App\Models\{Product,Category};
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
class ProductManager extends Component {
 use WithPagination;
 #[Url(except:'')] public string $search='';
 #[Url(except:'')] public string $category='';
 #[Url(except:'')] public string $status='';
 #[Url(except:'')] public string $stockFilter='';
 #[Url(except:'name')] public string $sort='name';
 #[Url(except:'asc')] public string $direction='asc';
 public int $revision=0;
 public string $notice='';
 public function boot(): void {abort_unless(auth()->user()?->can('access-admin'),403);}
 public function updated($property): void {if(in_array($property,['search','category','status','stockFilter','sort','direction'],true))$this->resetPage();}
 public function quickStatus(string $status): void {$this->status=in_array($status,['active','inactive','draft'],true)?$status:'';$this->resetPage();}
 public function clearFilters(): void {$this->reset('search','category','status','stockFilter');$this->resetPage();}
 public function sortBy(string $column): void {
  if(!in_array($column,['sku','name','status'],true))return;
  $this->direction=$this->sort===$column&&$this->direction==='asc'?'desc':'asc';$this->sort=$column;$this->resetPage();
 }
 public function setActive(int $id,bool $active): void {
  Product::findOrFail($id)->update(['status'=>$active?'active':'inactive']);
  $this->notice=$active?'Producto activado.':'Producto desactivado.';$this->revision++;
 }
 public function deleteProduct(int $id): void {
  Product::findOrFail($id)->delete();$this->notice='Producto eliminado del catálogo.';$this->revision++;$this->resetPage();
 }
 public function render() {
  $term=mb_substr(trim($this->search),0,255);
  $query=Product::query()->with(['primaryCategory','priceSnapshots'=>fn($q)=>$q->whereHas('pricePublication',fn($q)=>$q->where('is_active',true)->where('status','published')->whereHas('market',fn($q)=>$q->where('code','MX')))->latest('price_publication_id')])
   ->withSum('inventoryBalances as on_hand','qty_on_hand')->withSum('inventoryBalances as reserved','qty_reserved')
   ->when($term!=='',fn($q)=>$q->where(fn($q)=>$q->where('name','like',"%{$term}%")->orWhere('sku','like',"%{$term}%")))
   ->when(ctype_digit($this->category),fn($q)=>$q->where('primary_category_id',(int)$this->category))
   ->when(in_array($this->status,['active','inactive','draft'],true),fn($q)=>$q->where('status',$this->status));
  if($this->stockFilter==='in')$query->whereHas('inventoryBalances',fn($q)=>$q->whereColumn('qty_on_hand','>','qty_reserved'));
  if($this->stockFilter==='out')$query->whereDoesntHave('inventoryBalances',fn($q)=>$q->whereColumn('qty_on_hand','>','qty_reserved'));
  $products=$query->orderBy(in_array($this->sort,['sku','name','status'],true)?$this->sort:'name',$this->direction==='desc'?'desc':'asc')->orderBy('id')->paginate(10);
  $tableKey=hash('sha256',json_encode([$products->currentPage(),$this->search,$this->category,$this->status,$this->stockFilter,$this->sort,$this->direction,$this->revision]));
  return view('livewire.admin.products.product-manager',['products'=>$products,'categories'=>Category::orderBy('name')->get(['id','name']),'tableKey'=>$tableKey]);
 }
}
