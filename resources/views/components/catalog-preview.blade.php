@props(['compact' => false])
<section id="catalogo" @class(['catalog-shop', 'catalog-compact' => $compact]) x-data="catalog({ compact: @js($compact) })" x-id="['catalog-filters', 'catalog-search', 'catalog-sort', 'catalog-price', 'catalog-brand', 'catalog-categories']" aria-labelledby="catalog-title">
    <div class="catalog-section-heading">
        <div><h2 id="catalog-title">Equipo para tomar otro camino.</h2><p>Del primer sendero a la última noche bajo las estrellas.</p></div>
        @if($compact)<a class="catalog-text-link" href="{{ route('store.catalog') }}">Ver todo el catálogo <x-store-icon name="arrow" size="18" /></a>@else<span class="catalog-selection-note"><x-store-icon name="mountain" size="19" /> Selección Refugio Outdoor</span>@endif
    </div>
    <div class="catalog-toolbar">
        <div class="catalog-category-picker" @click.outside="categoryMenuOpen = false" @keydown.escape.prevent.stop="closeCategoryMenu()">
            <button type="button" class="catalog-category-trigger" x-ref="categoryTrigger" :aria-expanded="categoryMenuOpen" :aria-controls="$id('catalog-categories')" aria-haspopup="dialog" @click="toggleCategoryMenu()">
                <x-store-icon name="grid" size="18" />
                <span><small>Categoría</small><strong x-text="selectedCategoryName"></strong></span>
                <x-store-icon name="chevron" size="14" class="catalog-category-chevron" />
            </button>
            <section class="catalog-category-dropdown" x-show="categoryMenuOpen" x-cloak :id="$id('catalog-categories')" role="dialog" aria-label="Elegir categoría">
                <header><h3>Explorar categorías</h3><span x-text="categories.length"></span><button type="button" aria-label="Cerrar categorías" @click="closeCategoryMenu()"><x-store-icon name="close" size="17" /></button></header>
                <label class="catalog-category-search"><x-store-icon name="search" size="17" /><span class="sr-only">Buscar categoría</span><input type="search" x-ref="categorySearch" x-model="categoryQuery" placeholder="Encuentra una categoría" autocomplete="off"></label>
                <div class="catalog-category-options" role="group" aria-label="Categorías disponibles">
                    <button type="button" class="catalog-category-all" :aria-pressed="!category" @click="chooseCategory('')"><span>Todas las categorías</span><x-store-icon name="check" size="16" x-show="!category" /></button>
                    <template x-for="group in filteredCategories" :key="group.slug"><button type="button" :aria-pressed="category === group.slug" @click="chooseCategory(group.slug)"><span x-text="group.name"></span><x-store-icon name="check" size="16" x-show="category === group.slug" /></button></template>
                    <p class="catalog-category-no-results" x-show="!filteredCategories.length" x-cloak>No hay categorías con ese nombre.</p>
                </div>
                <footer>Selecciona una categoría para afinar el catálogo.</footer>
            </section>
        </div>
        <label class="catalog-search" :for="$id('catalog-search')"><x-store-icon name="search" /><span class="sr-only">Buscar</span><input :id="$id('catalog-search')" type="search" x-model="query" x-on:blur="applyFilters()" x-on:keydown.enter.prevent="$event.target.blur()" placeholder="Buscar productos..." autocomplete="off"></label>
        <button class="catalog-filter-toggle" type="button" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen" :aria-controls="$id('catalog-filters')"><x-store-icon name="filter" size="18" /><span>Filtros</span><span class="catalog-filter-count" x-show="activeFilters.length" x-text="activeFilters.length" x-cloak></span></button>
        <label class="catalog-sort" :for="$id('catalog-sort')"><span>Ordenar por</span><select :id="$id('catalog-sort')" aria-label="Ordenar productos" x-model="sort"><option value="">Destacados</option><option value="asc">Menor precio</option><option value="desc">Mayor precio</option></select></label>
        <button class="catalog-view-toggle" type="button" aria-label="Cambiar vista" :title="list ? 'Ver cuadrícula' : 'Ver lista'" :aria-pressed="list" @click="toggleList()"><span x-show="!list"><x-store-icon name="list" /></span><span x-show="list" x-cloak><x-store-icon name="grid" /></span></button>
    </div>
    <div class="catalog-active-filters" x-show="activeFilters.length" x-cloak><template x-for="filter in activeFilters" :key="filter.key"><button type="button" @click="removeFilter(filter.key)" :aria-label="'Quitar filtro: ' + filter.label"><span x-text="filter.label"></span><x-store-icon name="close" size="13" /></button></template><button class="catalog-reset" type="button" @click="reset()">Limpiar filtros</button></div>
    <div class="catalog-body" :class="{ 'has-filters': filtersOpen }">
        <aside class="catalog-refine" :id="$id('catalog-filters')" x-show="filtersOpen" x-cloak aria-label="Filtros del catálogo">
            <div class="catalog-refine-title"><h3>Afina tu búsqueda</h3><button type="button" @click="filtersOpen = false" class="catalog-icon-button" aria-label="Cerrar filtros"><x-store-icon name="close" size="17" /></button></div>
            <fieldset><legend>Categoría</legend><label class="sr-only" :for="$id('catalog-categories') + '-sidebar'">Filtrar por categoría</label><select :id="$id('catalog-categories') + '-sidebar'" x-model="category"><option value="">Todas las categorías</option><template x-for="group in categories" :key="group.slug"><option :value="group.slug" x-text="group.name"></option></template></select></fieldset>
            <fieldset><legend>Marca</legend><label class="sr-only" :for="$id('catalog-brand')">Marca</label><select :id="$id('catalog-brand')" x-model="brand"><option value="">Todas las marcas</option><template x-for="option in brands" :key="option.slug"><option :value="option.slug" x-text="option.name"></option></template></select></fieldset>
            <fieldset><legend>Tu presupuesto</legend><label :for="$id('catalog-price')">Precio máximo estimado <span x-text="$store.shop.currency"></span></label><input :id="$id('catalog-price')" type="number" min="0" step="0.01" x-model="maximum" x-on:blur="applyFilters()" x-on:keydown.enter.prevent="$event.target.blur()" placeholder="Sin límite"></fieldset>
            <fieldset><legend>Disponibilidad</legend><label class="catalog-stock-option"><input type="checkbox" x-model="stock"><span>Solo disponibles</span></label></fieldset>
            <button class="catalog-text-link" type="button" @click="reset()">Limpiar filtros <x-store-icon name="close" size="14" /></button>
            <p class="catalog-refine-note"><x-store-icon name="mountain" size="26" /> Menos cosas.<br>Más camino.</p>
        </aside>
        <div class="catalog-results">
            <div class="catalog-results-heading"><span role="status" aria-live="polite" x-text="$store.shop.totalProducts + ($store.shop.totalProducts === 1 ? ' producto' : ' productos')"></span><span>Precios estimados en <strong x-text="$store.shop.currency"></strong></span></div>
            <p role="status" x-show="$store.shop.loading" class="page-note py-4">Cargando productos…</p>
            <p role="alert" x-show="$store.shop.error" x-cloak class="blade-status"><span x-text="$store.shop.error"></span> <button type="button" class="text-button" @click="$store.shop.loadProducts($store.shop.currentPage)">Reintentar</button></p>
            <p role="status" x-show="$store.shop.filtersError" x-text="$store.shop.filtersError" class="page-note" x-cloak></p>
            <div class="catalog-product-grid" :class="{ 'is-list': list }" :aria-busy="$store.shop.loading">
                <template x-for="product in visible" :key="product.id"><article class="product-card">
                    <div class="catalog-product-visual">
                        <a class="catalog-product-image" :href="'/productos/' + encodeURIComponent(product.id)" :aria-label="'Ver ' + product.name">
                            <x-product-sprite x-show="!imageUrl(product)" />
                            <template x-if="imageUrl(product)"><img class="catalog-external-image" :src="imageUrl(product)" :alt="product.name" loading="lazy" width="640" height="640" referrerpolicy="no-referrer" x-on:error="imageError(product)"></template>
                        </a>
                        <span class="catalog-availability" :class="{ 'is-unavailable': !product.available }"><span aria-hidden="true"></span><span x-text="product.available ? 'Disponible' : 'Agotado'"></span></span>
                        <button class="catalog-quickview" type="button" @click="openProduct(product)" :aria-label="'Vista rápida de ' + product.name"><x-store-icon name="eye" size="17" /><span>Vista rápida</span></button>
                    </div>
                    <div class="product-copy">
                        <p class="catalog-product-meta"><span x-text="product.category"></span><span x-text="product.brand"></span></p>
                        <h3><a :href="'/productos/' + encodeURIComponent(product.id)" x-text="product.name"></a></h3>
                        <p class="catalog-product-description" x-text="product.detail || ''"></p>
                        <div class="catalog-product-purchase"><div><strong class="catalog-product-price" x-text="$store.shop.price(product.prices[$store.shop.currency])"></strong><small class="catalog-secondary-price" x-text="$store.shop.price(product.prices[$store.shop.currency === 'MXN' ? 'USD' : 'MXN'], $store.shop.currency === 'MXN' ? 'USD' : 'MXN')"></small></div><button class="catalog-add-button" type="button" @click="$store.shop.change(product.id, 1)" :disabled="!product.available || ($store.shop.quantities[product.id] ?? 0) >= 9"><x-store-icon name="plus" size="19" /><span x-text="!product.available ? 'Agotado' : 'Agregar a bolsa'"></span></button></div>
                    </div>
                </article></template>
            </div>
            <div class="catalog-empty" x-show="!$store.shop.loading && !$store.shop.error && visible.length === 0" x-cloak><x-store-icon name="search" size="34" /><h3>No encontramos ese equipo.</h3><p>Prueba otra palabra o elimina algún filtro para seguir explorando.</p><button class="catalog-dark-button" type="button" @click="reset()">Ver todos los productos <x-store-icon name="arrow" size="17" /></button></div>
            <nav class="catalog-pagination" aria-label="Paginación de productos" x-show="$store.shop.lastPage > 1 && !$store.shop.error" x-cloak>
                <button type="button" class="store-button" :disabled="$store.shop.loading || $store.shop.currentPage <= 1" @click="$store.shop.loadProducts($store.shop.currentPage - 1)">Anterior</button>
                <span role="status" x-text="'Página ' + $store.shop.currentPage + ' de ' + $store.shop.lastPage"></span>
                <button type="button" class="store-button" :disabled="$store.shop.loading || $store.shop.currentPage >= $store.shop.lastPage" @click="$store.shop.loadProducts($store.shop.currentPage + 1)">Siguiente</button>
            </nav>
            <p class="catalog-demo-note">Precios y disponibilidad consultados al servidor. El importe final se confirma al comprar.</p>
        </div>
    </div>
    <dialog class="catalog-quickview-dialog" x-ref="quickview" aria-labelledby="quickview-title" @click="if ($event.target === $refs.quickview) $refs.quickview.close()" @close="selected = null">
        <button type="button" class="catalog-dialog-close" aria-label="Cerrar vista rápida" @click="$refs.quickview.close()"><x-store-icon name="close" /></button>
        <template x-if="selected"><div class="catalog-quickview-content">
            <div class="catalog-quickview-image"><x-product-sprite product="selected" x-show="!imageUrl(selected)" /><template x-if="imageUrl(selected)"><img :src="imageUrl(selected)" :alt="selected.name" referrerpolicy="no-referrer" x-on:error="imageError(selected)"></template></div>
            <div class="catalog-quickview-copy"><p class="catalog-product-meta" x-text="selected.category + ' · ' + selected.brand"></p><h2 id="quickview-title" x-text="selected.name"></h2><p x-text="selected.detail"></p><p class="catalog-product-price" x-text="$store.shop.price(selected.prices[$store.shop.currency])"></p><p class="catalog-quickview-status" x-text="selected.available ? 'Disponible' : 'Agotado'"></p><button type="button" class="catalog-dark-button" :disabled="!selected.available || ($store.shop.quantities[selected.id] ?? 0) >= 9" @click="$store.shop.change(selected.id, 1)"><x-store-icon name="bag" size="18" /> Agregar a bolsa</button><a class="catalog-text-link" :href="'/productos/' + encodeURIComponent(selected.id)">Ver todos los detalles <x-store-icon name="arrow" size="16" /></a></div>
        </div></template>
    </dialog>
</section>
