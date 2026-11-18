<div class="store-wrap blade-page">
 <div class="blade-admin-workspace">
  <x-admin.navigation />
  <div class="ro-dashboard min-w-0">
   <header class="ro-hero"><div><span class="ro-demo">Administración</span><h1>Categorías</h1><p>Organiza y consulta las categorías reales del catálogo.</p></div></header>
   <section class="ro-panel">
    <div class="ro-filters">
     <label>Buscar categoría<input type="search" wire:model.blur="search" x-on:keydown.enter.prevent="$event.target.blur()" maxlength="150" placeholder="Nombre o slug"></label>
     <label>Estado<select wire:model.live="status"><option value="">Todos los estados</option><option value="active">Activas</option><option value="inactive">Inactivas</option></select></label>
     <label>Categoría superior<select wire:model.live="parent"><option value="">Todas</option><option value="root">Sin categoría superior</option>@foreach($parents as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</select></label>
     <button class="ro-button" type="button" wire:click="clearFilters">Limpiar filtros</button>
    </div>
    <p class="ro-footnote" role="status" wire:loading>Consultando categorías…</p>
    <div class="blade-table-wrap" wire:loading.class="opacity-50">
     <table class="blade-table"><caption class="sr-only">Categorías del catálogo</caption><thead><tr><th>Nombre</th><th>Slug</th><th>Categoría superior</th><th>Productos principales</th><th>Estado</th><th>Catálogo</th></tr></thead><tbody>
      @forelse($categories as $category)
       <tr wire:key="category-{{ $category->id }}"><td>{{ $category->name }}</td><td>{{ $category->slug }}</td><td>{{ $category->parent?->name ?? 'Sin categoría superior' }}</td><td>{{ number_format($category->primary_products_count) }}</td><td><span class="ro-tag">{{ $category->active ? 'Activa' : 'Inactiva' }}</span></td><td><a href="{{ route('admin.products.index', ['category' => $category->id]) }}">Ver productos</a></td></tr>
      @empty
       <tr><td colspan="6">No hay categorías que coincidan con los filtros.</td></tr>
      @endforelse
     </tbody></table>
    </div>
    <div class="mt-6">{{ $categories->links() }}</div>
   </section>
  </div>
 </div>
</div>
