<div class="store-wrap blade-page"><div class="blade-admin-workspace"><x-admin.navigation /><div class="ro-dashboard min-w-0">
<header class="ro-hero"><div><h1>Productos</h1><p>Administra el catálogo, los precios y las existencias.</p></div><a class="ro-button product-add-button" href="{{ route('admin.products.create') }}">Nuevo producto</a></header>
<section class="ro-panel product-table-panel">
@if($notice || session('status'))<p class="ro-notice" role="status">{{ $notice ?: session('status') }}</p>@endif
<div class="product-quick-filters" role="group" aria-label="Filtros rápidos">
@foreach([''=>'Todos','active'=>'Activos','inactive'=>'Inactivos','draft'=>'Borradores'] as $value=>$label)<button type="button" class="{{ $status === $value ? 'is-selected' : '' }}" aria-pressed="{{ $status === $value ? 'true' : 'false' }}" wire:click="quickStatus('{{ $value }}')">{{ $label }}</button>@endforeach
</div>
<div class="ro-filters">
<label>Buscar<input type="search" wire:model.blur.live="search" x-on:keydown.enter.prevent="$event.target.blur()" maxlength="255" placeholder="Nombre o SKU"></label>
<label>Categoría<select wire:model.live="category"><option value="">Todas las categorías</option>@foreach($categories as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></label>
<label>Estado<select wire:model.live="status"><option value="">Todos</option><option value="active">Activos</option><option value="inactive">Inactivos</option><option value="draft">Borradores</option></select></label>
<label>Disponibilidad<select wire:model.live="stockFilter"><option value="">Todo el stock</option><option value="in">Con stock</option><option value="out">Sin stock</option></select></label>
<button class="ro-button" type="button" wire:click="clearFilters">Limpiar filtros</button>
</div>
<div class="blade-actions mb-4"><span>Ordenar:</span>@foreach(['sku'=>'SKU','name'=>'Nombre','status'=>'Estado'] as $value=>$label)<button class="ro-button" type="button" wire:click="sortBy('{{ $value }}')">{{ $label }} {{ $sort === $value ? ($direction === 'asc' ? '↑' : '↓') : '' }}</button>@endforeach</div>
<p role="status" wire:loading>Actualizando productos…</p>
<div wire:key="products-table-{{ $tableKey }}"><div class="ro-table-region" wire:ignore x-data="refugioLivewireTable">
<table class="display ro-table" x-ref="table" style="width:100%"><thead><tr><th>SKU</th><th>Producto</th><th>Categoría</th><th>Precio USD</th><th>Precio MXN</th><th>Stock</th><th>Activo</th><th>Acciones</th></tr></thead><tbody>
@foreach($products as $product)
@php($price=$product->priceSnapshots->first())
<tr><td>{{ $product->sku }}</td><td>{{ $product->name }}</td><td>{{ $product->primaryCategory?->name ?? 'Sin categoría' }}</td><td>{{ $price ? number_format((float)$price->price_usd,2).' USD' : 'Sin precio' }}</td><td>{{ $price ? number_format((float)$price->price_mxn,2).' MXN' : 'Sin precio' }}</td><td>{{ max(0,($product->on_hand ?? 0)-($product->reserved ?? 0)) }}</td>
<td><button type="button" class="product-switch" role="switch" aria-checked="{{ $product->status === 'active' ? 'true' : 'false' }}" aria-label="Activar producto {{ $product->id }}" wire:click="setActive({{ $product->id }}, {{ $product->status === 'active' ? 'false' : 'true' }})" wire:loading.attr="disabled"><span class="product-switch-track"><span></span></span><span>{{ ['active'=>'Activo','inactive'=>'Inactivo','draft'=>'Borrador'][$product->status] ?? $product->status }}</span></button></td>
<td><div class="product-row-actions"><a href="{{ route('admin.products.show',$product) }}">Detalle</a><a href="{{ route('admin.products.edit',$product) }}">Editar</a><button class="product-delete" type="button" wire:click="deleteProduct({{ $product->id }})" wire:confirm="¿Eliminar este producto del catálogo?" wire:loading.attr="disabled">Eliminar</button></div></td></tr>
@endforeach
</tbody></table>
</div></div>
<div class="mt-5">{{ $products->links() }}</div>
</section></div></div></div>
