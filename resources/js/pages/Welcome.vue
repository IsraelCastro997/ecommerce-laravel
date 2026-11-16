<script setup lang="ts">
import { categories, formatPrice, products, type Currency, type Product } from '@/mocks/store';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowLeft,
    ArrowRight,
    Backpack,
    Compass,
    Droplet,
    Grid2X2,
    List,
    Mountain,
    PackageCheck,
    Search,
    ShieldCheck,
    ShoppingBag,
    Tent,
    Truck,
    UserRound,
    X,
    Zap,
} from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

const page = usePage<{ auth: { user: import('@/types').User | null } }>();
const auth = computed(() => page.props.auth.user);
const currency = ref<Currency>('MXN');
const query = ref('');
const category = ref('');
const brand = ref('');
const stock = ref(false);
const maximum = ref<number | ''>('');
const sort = ref('');
const list = ref(false);
const searchInput = ref<HTMLInputElement>();
const dialog = ref<HTMLDialogElement>();
const panel = ref<'product' | 'cart' | 'checkout' | 'help'>('cart');
const active = ref<Product>();
const info = ref('Preguntas frecuentes');
const bag = ref<Record<string, number>>({});
const items = computed(() => products.filter((product) => bag.value[product.id]));
const itemCount = computed(() => Object.values(bag.value).reduce((count, quantity) => count + quantity, 0));
const categoryIcons = [Backpack, Tent, Droplet, Zap];

const visible = computed(() => {
    const phrase = query.value.trim().toLocaleLowerCase('es-MX');
    const rows = products.filter(
        (product) =>
            (!phrase ||
                [product.name, product.category, product.brand, product.id].some((value) => value.toLocaleLowerCase('es-MX').includes(phrase))) &&
            (!category.value || product.category === category.value) &&
            (!brand.value || product.brand === brand.value) &&
            (!stock.value || product.available) &&
            (maximum.value === '' || product.prices[currency.value] <= Number(maximum.value)),
    );
    if (sort.value === 'ascending') rows.sort((a, b) => a.prices[currency.value] - b.prices[currency.value]);
    if (sort.value === 'descending') rows.sort((a, b) => b.prices[currency.value] - a.prices[currency.value]);
    return rows;
});

function resetFilters() {
    query.value = '';
    category.value = '';
    brand.value = '';
    stock.value = false;
    maximum.value = '';
    sort.value = '';
}

async function openPanel(next: typeof panel.value, product?: Product) {
    panel.value = next;
    active.value = product;
    if (!dialog.value?.open) dialog.value?.showModal();
    await nextTick();
    dialog.value?.querySelector<HTMLButtonElement>('.close-panel')?.focus();
}

function changeQuantity(product: Product, delta: number) {
    const quantity = Math.min(9, (bag.value[product.id] || 0) + delta);
    if (quantity > 0) bag.value[product.id] = quantity;
    else delete bag.value[product.id];
}

function showHelp(topic: string) {
    info.value = topic;
    void openPanel('help');
}
</script>

<template>
    <Head title="Refugio Outdoor · Equipo para explorar" />
    <div class="refugio">
        <a class="skip-navigation" href="#contenido">Saltar al contenido</a>
        <div class="store-announcement"><Compass :size="15" /> Tienda de demostración · Precios y disponibilidad ilustrativos</div>
        <header class="store-header">
            <div class="store-wrap header-inner">
                <Link class="store-mark" href="/" aria-label="Refugio Outdoor, inicio"
                    ><Mountain :size="29" /> <span>REFUGIO<small>OUTDOOR</small></span></Link
                >
                <nav class="main-links" aria-label="Navegación principal">
                    <a href="#catalogo">Catálogo</a><a href="#categorias">Categorías</a><a href="#ayuda">Ayuda</a>
                </nav>
                <div class="store-actions">
                    <select v-model="currency" aria-label="Moneda" @change="maximum = ''">
                        <option>MXN</option>
                        <option>USD</option>
                    </select>
                    <button class="icon-button" type="button" aria-label="Buscar en el catálogo" @click="searchInput?.focus()">
                        <Search :size="20" />
                    </button>
                    <Link class="icon-button" :href="auth ? route('dashboard') : route('login')" :aria-label="auth ? 'Mi cuenta' : 'Iniciar sesión'"
                        ><UserRound :size="20"
                    /></Link>
                    <button
                        class="icon-button bag-button"
                        type="button"
                        :aria-label="'Abrir carrito, ' + itemCount + ' artículos'"
                        @click="openPanel('cart')"
                    >
                        <ShoppingBag :size="20" /><span aria-hidden="true">{{ itemCount }}</span>
                    </button>
                </div>
            </div>
        </header>

        <main id="contenido" class="store-wrap" tabindex="-1">
            <section class="store-hero">
                <div class="hero-copy">
                    <p class="eyebrow"><span></span> EQUIPO PARA EXPLORAR</p>
                    <h1>El camino<br />empieza afuera.</h1>
                    <p>Prepara tu próxima salida con equipo para el sendero, el campamento y todo lo que hay en medio.</p>
                    <a class="store-button primary" href="#catalogo">Explorar catálogo <ArrowRight :size="18" /></a>
                </div>
                <a class="hero-down" href="#categorias" aria-label="Ir a categorías"><ArrowDown :size="19" /></a>
            </section>

            <section id="categorias" class="store-section" aria-labelledby="category-title">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">ENCUENTRA TU EQUIPO</p>
                        <h2 id="category-title">Explora por categoría</h2>
                    </div>
                </div>
                <div class="category-grid">
                    <a
                        v-for="(group, index) in categories"
                        :key="group"
                        href="#catalogo"
                        @click="
                            resetFilters();
                            category = group;
                        "
                    >
                        <span class="category-icon"><component :is="categoryIcons[index]" :size="24" /></span><strong>{{ group }}</strong
                        ><ArrowRight :size="17" class="category-arrow" />
                    </a>
                </div>
            </section>

            <section id="catalogo" class="store-section" aria-labelledby="catalog-title">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">CATÁLOGO DEMO</p>
                        <h2 id="catalog-title">Esenciales para salir</h2>
                    </div>
                    <div class="view-controls" aria-label="Vista del catálogo">
                        <button class="icon-button" type="button" aria-label="Vista en cuadrícula" :aria-pressed="!list" @click="list = false">
                            <Grid2X2 :size="19" />
                        </button>
                        <button class="icon-button" type="button" aria-label="Vista en lista" :aria-pressed="list" @click="list = true">
                            <List :size="21" />
                        </button>
                    </div>
                </div>
                <div class="catalog-layout">
                    <aside class="catalog-filters" aria-label="Filtros del catálogo">
                        <label
                            >Buscar
                            <input ref="searchInput" v-model="query" type="search" list="product-suggestions" placeholder="Producto, marca o SKU"
                        /></label>
                        <datalist id="product-suggestions"><option v-for="product in products" :key="product.id" :value="product.name" /></datalist>
                        <label
                            >Categoría
                            <select v-model="category">
                                <option value="">Todas</option>
                                <option v-for="group in categories" :key="group">{{ group }}</option>
                            </select></label
                        >
                        <label
                            >Marca
                            <select v-model="brand">
                                <option value="">Todas</option>
                                <option>Refugio</option>
                                <option>Cumbre</option>
                            </select></label
                        >
                        <label>Precio máximo ({{ currency }}) <input v-model="maximum" type="number" min="0" placeholder="Sin límite" /></label>
                        <label class="stock-filter"><input v-model="stock" type="checkbox" /> Solo disponibles</label>
                        <button class="text-button" type="button" @click="resetFilters">Limpiar filtros</button>
                    </aside>
                    <div>
                        <div class="results-bar">
                            <p role="status">{{ visible.length }} {{ visible.length === 1 ? 'producto' : 'productos' }} de ejemplo</p>
                            <select v-model="sort" aria-label="Ordenar productos">
                                <option value="">Destacados</option>
                                <option value="ascending">Menor precio</option>
                                <option value="descending">Mayor precio</option>
                            </select>
                        </div>
                        <div v-if="visible.length" class="product-grid" :class="{ 'product-list': list }">
                            <article v-for="product in visible" :key="product.id" class="product-card">
                                <div class="product-photo" role="img" :aria-label="product.name" :style="{ backgroundPosition: product.image }"></div>
                                <div class="product-copy">
                                    <p>{{ product.brand }} · {{ product.category }}</p>
                                    <h3>
                                        <button type="button" @click="openPanel('product', product)">{{ product.name }}</button>
                                    </h3>
                                    <strong>{{ formatPrice(product.prices[currency], currency) }}</strong
                                    ><small>Precio estimado</small>
                                </div>
                            </article>
                        </div>
                        <p v-else class="store-empty">
                            No encontramos productos de ejemplo con esos filtros.
                            <button type="button" class="text-button" @click="resetFilters">Ver todo</button>
                        </p>
                        <p class="page-note">Página de muestra · el catálogo real usará paginación de Laravel.</p>
                    </div>
                </div>
            </section>

            <section class="store-benefits" aria-label="Información de compra">
                <div><Truck /><strong>Envíos</strong><span>Opciones por confirmar en checkout</span></div>
                <div><ShieldCheck /><strong>Pagos</strong><span>Integración en preparación</span></div>
                <div><PackageCheck /><strong>Devoluciones</strong><span>Condiciones pendientes de publicar</span></div>
            </section>
        </main>
        <footer id="ayuda" class="store-footer">
            <div class="store-wrap">
                <div class="store-mark">
                    <Mountain :size="28" /> <span>REFUGIO<small>OUTDOOR</small></span>
                </div>
                <nav aria-label="Información de la tienda">
                    <button
                        v-for="topic in ['Contacto', 'Preguntas frecuentes', 'Envíos y devoluciones', 'Términos', 'Privacidad']"
                        :key="topic"
                        type="button"
                        @click="showHelp(topic)"
                    >
                        {{ topic }}
                    </button>
                </nav>
            </div>
        </footer>
        <dialog ref="dialog" class="store-dialog" aria-labelledby="panel-title">
            <button class="icon-button close-panel" type="button" aria-label="Cerrar" @click="dialog?.close()"><X :size="22" /></button>
            <p class="eyebrow">DEMOSTRACIÓN</p>
            <template v-if="panel === 'product' && active">
                <h2 id="panel-title">{{ active.name }}</h2>
                <div class="product-photo" role="img" :aria-label="active.name" :style="{ backgroundPosition: active.image }"></div>
                <p>{{ active.detail }}</p>
                <p>{{ active.id }} · {{ active.brand }} · Origen: {{ active.origin }}</p>
                <p>
                    <strong>{{ formatPrice(active.prices[currency], currency) }}</strong> · Precio estimado
                </p>
                <p>{{ active.available ? 'Disponible en la muestra' : 'Agotado en la muestra' }}</p>
                <button
                    class="store-button primary"
                    type="button"
                    :disabled="!active.available"
                    @click="
                        changeQuantity(active, 1);
                        openPanel('cart');
                    "
                >
                    Agregar al carrito demo <ShoppingBag :size="18" />
                </button>
                <h3 class="related-heading">También en el catálogo</h3>
                <ul>
                    <li v-for="product in products.filter((item) => item.id !== active?.id).slice(0, 2)" :key="product.id">
                        <button class="text-button" type="button" @click="openPanel('product', product)">{{ product.name }}</button>
                    </li>
                </ul>
            </template>
            <template v-else-if="panel === 'cart'">
                <h2 id="panel-title">Tu carrito</h2>
                <p v-if="!items.length" class="store-empty">Tu carrito de demostración está vacío.</p>
                <ul v-else class="cart-list">
                    <li v-for="product in items" :key="product.id">
                        <strong>{{ product.name }}</strong
                        ><span>{{ formatPrice(product.prices[currency], currency) }} por unidad · estimado</span>
                        <div class="cart-controls">
                            <button :aria-label="'Quitar una unidad de ' + product.name" type="button" @click="changeQuantity(product, -1)">−</button
                            ><output :aria-label="'Cantidad de ' + product.name">{{ bag[product.id] }}</output
                            ><button
                                :aria-label="'Agregar una unidad de ' + product.name"
                                :disabled="bag[product.id] >= 9"
                                type="button"
                                @click="changeQuantity(product, 1)"
                            >
                                +</button
                            ><button class="text-button" type="button" @click="delete bag[product.id]">Eliminar</button>
                        </div>
                    </li>
                </ul>
                <p>Subtotal, envío e impuestos pendientes de confirmación.</p>
                <button v-if="items.length" class="store-button primary" type="button" @click="openPanel('checkout')">
                    Ver checkout demo <ArrowRight :size="18" />
                </button>
            </template>
            <template v-else-if="panel === 'checkout'">
                <h2 id="panel-title">Confirmar pedido</h2>
                <p>Vista previa de {{ itemCount }} artículos en {{ currency }}. Introduce solo datos ficticios para explorar el formulario.</p>
                <div class="checkout-fields">
                    <label>Dirección de entrega <input placeholder="Calle y número" /></label>
                    <label>Código postal <input inputmode="numeric" placeholder="00000" /></label>
                    <label
                        >Envío
                        <select>
                            <option>Consultar opciones al integrar</option>
                        </select></label
                    >
                    <label>Cupón <input placeholder="Código de muestra" /></label>
                </div>
                <p>Resumen pendiente de validación del servidor.</p>
                <div class="cart-controls">
                    <button class="text-button" type="button" @click="openPanel('cart')"><ArrowLeft :size="17" /> Volver al carrito</button
                    ><button class="store-button primary" type="button" disabled>Continuar al pago</button>
                </div>
            </template>
            <template v-else>
                <h2 id="panel-title">{{ info }}</h2>
                <p v-if="info === 'Preguntas frecuentes'">
                    Puedes explorar el catálogo, cambiar la moneda de los precios de muestra y probar el carrito. Las compras estarán disponibles
                    cuando se integre el servicio de pedidos.
                </p>
                <p v-else>La información de {{ info.toLocaleLowerCase('es-MX') }} se publicará antes de habilitar pedidos.</p>
            </template>
        </dialog>
    </div>
</template>

<style src="../../css/store.css"></style>
