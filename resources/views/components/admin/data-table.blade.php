@props(['kind' => 'products'])
<section class="ro-panel product-table-panel" @click="handleAction($event)" x-data="refugioDataTable(@js($kind))" aria-label="{{ $kind === 'products' ? 'Productos' : 'Pedidos de demostración' }}">
 <header class="ro-section-heading"><div><h2>{{ $kind === 'products' ? 'Catálogo de productos' : 'Últimos pedidos' }}</h2><p>{{ $kind === 'products' ? 'Busca productos y administra su disponibilidad' : 'Datos de ejemplo' }}</p></div><span class="ro-tag">Catálogo</span></header>
 @if($kind === 'products')
 <div class="product-quick-filters" role="group" aria-label="Filtros rápidos de productos">
 @foreach([''=>'Todos','active'=>'Activos','inactive'=>'Inactivos','draft'=>'Borradores'] as $value=>$label)<button type="button" :class="{ 'is-selected': status === '{{ $value }}' }" :aria-pressed="status === '{{ $value }}'" @click="quickStatus('{{ $value }}')">{{ $label }}</button>@endforeach
 </div>
 @endif
 <div class="ro-filters">
  <label>Buscar<input type="search" x-model="query" @blur="refresh()" @keydown.enter.prevent="$event.target.blur()" placeholder="Nombre, referencia o estado"></label>
  @if($kind === 'products')<label>Categoría<select x-model="category" @change="refresh()"><option value="">Todas las categorías</option>@foreach(\App\Models\Category::orderBy('name')->get(['id','name']) as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>@endif
  <label>Estado<select x-model="status" @change="refresh()"><option value="">Todos los estados</option><template x-for="value in statuses" :key="value"><option :value="value" x-text="value"></option></template></select></label>
  @if($kind === 'products')<label>Disponibilidad<select x-model="stockFilter" @change="refresh()"><option value="">Todo el stock</option><option value="in">Con stock</option><option value="out">Sin stock</option></select></label>@endif
  <button type="button" class="ro-button" @click="reset()">Limpiar filtros</button>
 </div>
 <p class="ro-notice" role="status" x-show="notice" x-text="notice" x-cloak></p>
 <p class="ro-error" role="alert" x-show="error" x-cloak><span x-text="error"></span> <button type="button" @click="refresh()">Reintentar</button></p>
 <div class="ro-table-region" role="region" aria-label="Tabla desplazable; usa las flechas para ver las columnas" tabindex="0" :aria-busy="loading" wire:ignore>
  <table x-ref="table" class="display ro-table" style="width:100%"><caption class="sr-only">{{ $kind === 'products' ? 'Productos' : 'Pedidos' }}. Ordena usando los encabezados. Paginación del servidor.</caption><thead><tr></tr></thead><tbody></tbody></table>
 </div>
 <p class="ro-footnote">{{ $kind === 'products' ? 'Paginación y filtros procesados por Laravel. Eliminar conserva el registro mediante soft delete.' : 'Vista de ejemplo.' }}</p>
</section>
