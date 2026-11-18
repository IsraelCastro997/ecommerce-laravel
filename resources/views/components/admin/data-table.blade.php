@props(['kind' => 'products'])
<section class="ro-panel" x-data="refugioDataTable(@js($kind))" aria-label="{{ $kind === 'products' ? 'Productos de demostración' : 'Pedidos de demostración' }}">
 <header class="ro-section-heading"><div><h2>{{ $kind === 'products' ? 'Catálogo de productos' : 'Últimos pedidos' }}</h2><p>Datos ficticios · Una página de ejemplo · Importes en MXN</p></div><span class="ro-tag">DataTables</span></header>
 <div class="ro-filters">
  <label>Buscar<input type="search" x-model="query" @input.debounce.350ms="refresh()" placeholder="Nombre, referencia o estado"></label>
  @if($kind === 'products')<label>Categoría<select x-model="category" @change="refresh()"><option value="">Todas las categorías</option><template x-for="value in categories" :key="value"><option :value="value" x-text="value"></option></template></select></label>@endif
  <label>Estado<select x-model="status" @change="refresh()"><option value="">Todos los estados</option><template x-for="value in statuses" :key="value"><option :value="value" x-text="value"></option></template></select></label>
  <button type="button" class="ro-button" @click="reset()">Limpiar filtros</button>
 </div>
 <p class="ro-error" role="alert" x-show="error" x-cloak><span x-text="error"></span> <button type="button" @click="refresh()">Reintentar</button></p>
 <div class="ro-table-region" role="region" aria-label="Tabla desplazable; usa las flechas para ver las columnas" tabindex="0" :aria-busy="loading" wire:ignore>
  <table x-ref="table" class="display ro-table" style="width:100%"><caption class="sr-only">{{ $kind === 'products' ? 'Productos' : 'Pedidos' }} ficticios. Ordena usando los encabezados. La API proporcionará la paginación.</caption><thead><tr></tr></thead><tbody></tbody></table>
 </div>
 <p class="ro-footnote">Muestra local de menos de 10 registros. La paginación está configurada para el servidor; no se divide una colección en el navegador.</p>
</section>
