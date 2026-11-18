import { externalProductImage } from './catalog-presentation.js';
import './dashboard-ui.js';
import './google-sign-in.js';
import { estimate, checkoutItems } from './checkout-presentation.js';

import '../css/fonts.css';
import '../css/app.css';
import '../css/store.css';
import '../css/dashboard.css';
import '../css/admin-preview.css';
import '../css/blade.css';
import '../css/catalog.css';
import '../css/checkout-result.css';

// Debe existir una ruta Laravel GET que responda: { "data": [productos] }.
// La respuesta debe cumplir el contrato de catalog-presentation.js y checkout-presentation.js.
const PRODUCTS_ENDPOINT = '/storefront/products';
const FILTERS_ENDPOINT = '/storefront/filters';
const CART_KEY = 'refugio:demo-cart:v1';

function read(key, fallback) {
   try {
      return JSON.parse(localStorage.getItem(key)) ?? fallback;
   } catch {
      return fallback;
   }
}

function save(key, value) {
   try {
      localStorage.setItem(key, JSON.stringify(value));
   } catch {
      // La página puede seguir funcionando sin persistencia local.
   }
}

function normalizeProduct(product) {
   if (
      !product ||
      !Number.isSafeInteger(product.product_id) ||
      product.product_id < 1 ||
      typeof product.name !== 'string'
   ) {
      throw new Error('Laravel devolvió un producto sin product_id o name válidos.');
   }

   // Comprueba el contrato de importes sin inventar precios ni identificadores.
   estimate([product], {}, 'MXN');
   estimate([product], {}, 'USD');

   return { ...product, id: String(product.product_id), image_url: product.image_url || product.image || '', brand: product.brand || '', category: product.category || '' };
}

function restoreQuantities() {
   const saved = read(CART_KEY, {});
   const cart = saved && typeof saved === 'object' && !Array.isArray(saved)
      ? saved
      : {};

   // Conservamos los artículos de otras páginas para no perderlos al paginar.
   // Solo restauramos cantidades válidas; todavía es un carrito local de demostración.
   return Object.fromEntries(
      Object.entries(cart)
         .filter(([id, quantity]) => /^\d+$/.test(id) && Number.isInteger(quantity) && quantity > 0)
         .map(([id, quantity]) => [id, Math.min(9, quantity)])
   );
}

// Livewire proporciona Alpine; no llamar Alpine.start() aquí.
document.addEventListener('alpine:init', () => {
   const Alpine = window.Alpine;
   let catalogController;
   let catalogRequest = 0;
   let initialProductsRequest;

   Alpine.data('themePicker', () => ({
      theme: 'light',
      init() {
         try {
            this.theme = localStorage.getItem('refugio:theme') ?? 'light';
         } catch {
            this.theme = 'light';
         }
         this.apply();
      },
      apply() {
         document.documentElement.classList.toggle('dark', this.theme === 'dark');
      },
      set(value) {
         this.theme = value;
         this.apply();
         try {
            localStorage.setItem('refugio:theme', value);
         } catch {
            // La preferencia se conserva durante esta sesión.
         }
      },
   }));

   let currency = 'MXN';
   try {
      currency = localStorage.getItem('refugio:display-currency') === 'USD' ? 'USD' : 'MXN';
   } catch {
      currency = 'MXN';
   }

   Alpine.store('shop', {
      currency,
      products: [],
      quantities: restoreQuantities(),
      notice: '',
      loading: false,
      error: '',
      currentPage: 1,
      lastPage: 1,
      totalProducts: 0, categories: [], brands: [], filters: {}, filtersError: '',

      productsLoaded: false,
      ensureProducts() {
         if (this.productsLoaded) return Promise.resolve();
         if (!initialProductsRequest) {
            initialProductsRequest = this.loadProducts().finally(() => { initialProductsRequest = null; });
         }
         return initialProductsRequest;
      },

      query: '',
      async searchProducts(query = '') {
         this.query = String(query).trim();
         return this.loadProducts(1);
      },

      async loadFilters() {
         try {
            const response = await fetch(FILTERS_ENDPOINT, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('No se pudieron cargar marcas y categorías.');
            const data = await response.json();
            this.categories = Array.isArray(data.categories) ? data.categories : [];
            this.brands = Array.isArray(data.brands) ? data.brands : [];
            this.filtersError = '';
         } catch (error) { this.filtersError = error.message; }
      },

      async loadProducts(page = 1) {
         catalogController?.abort();
         catalogController = new window.AbortController();
         const requestId = ++catalogRequest;
         this.loading = true;
         this.error = '';
         try {
            const url = new URL(PRODUCTS_ENDPOINT, window.location.origin);
            url.searchParams.set('page', String(page));
            url.searchParams.set('currency', this.currency);
            if (this.query) url.searchParams.set('q', this.query);
            for (const [key, value] of Object.entries(this.filters)) {
               if (value !== '' && value !== false && value != null) url.searchParams.set(key, String(value));
            }
            const response = await fetch(url, {
               credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: catalogController.signal,
            });
            if (!response.ok) {
               const details = await response.json().catch(() => ({}));
               throw new Error(Object.values(details.errors ?? {}).flat().join(' ') || `No se pudo cargar el catálogo (HTTP ${response.status}).`);
            }
            const payload = await response.json();
            if (!Array.isArray(payload.data) || payload.data.length > 100) throw new Error('El servidor debe devolver una página de productos en data.');
            const products = payload.data.map(normalizeProduct);
            if (requestId !== catalogRequest) return;
            this.products = products;
            this.productsLoaded = true;
            this.currentPage = Number(payload.current_page) || 1;
            this.lastPage = Number(payload.last_page) || 1;
            this.totalProducts = Number(payload.total ?? products.length);
         } catch (error) {
            if (error.name === 'AbortError' || requestId !== catalogRequest) return;
            this.error = error.message || 'No se pudo cargar el catálogo.';
            this.products = [];
            this.totalProducts = 0;
         } finally {
            if (requestId === catalogRequest) this.loading = false;
         }
      },

      get count() {
         return Object.values(this.quantities).reduce((sum, n) => sum + n, 0);
      },

      get items() {
         // Este carrito solo puede mostrar los productos cargados en la página actual.
         // La persistencia completa entre páginas requiere Cart/CartItem o un endpoint por IDs.
         return this.products.filter(product => this.quantities[product.id]);
      },

      get totals() {
         return estimate(this.products, this.quantities, this.currency);
      },

      get total() {
         return this.totals.subtotal;
      },

      estimateFor(currencyCode) {
         return estimate(this.products, this.quantities, currencyCode);
      },

      price(amount, currencyCode = this.currency) {
         return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: currencyCode,
            currencyDisplay: 'code',
         }).format(amount);
      },

      setCurrency(value) {
         this.currency = value === 'USD' ? 'USD' : 'MXN';
         try {
            localStorage.setItem('refugio:display-currency', this.currency);
         } catch {
            // Se mantiene la preferencia en memoria.
         }
      },

      change(id, delta) {
         const productId = String(id);
         const product = this.products.find(p => p.id === productId && p.available);
         if (!product) return;

         const next = { ...this.quantities };
         const quantity = delta === 'remove'
            ? 0
            : Math.max(0, Math.min(9, (next[productId] ?? 0) + delta));

         if (quantity) next[productId] = quantity;
         else delete next[productId];

         this.quantities = next;
         save(CART_KEY, next);
         this.notice = quantity
            ? `${product.name}: ${quantity} en la bolsa de demostración.`
            : 'Artículo retirado de la bolsa de demostración.';
      },

      clear() {
         this.quantities = {};
         save(CART_KEY, {});
         this.notice = 'Bolsa de demostración vaciada.';
      },
   });

   Alpine.data('checkoutPreview', (endpoint = '') => ({
      endpoint,
      email: '',
      feedback: '',
      busy: false,

      get previewItems() {
         const shop = Alpine.store('shop');
         return shop.items.map(product => ({
            product_id: product.product_id,
            quantity: shop.quantities[product.id],
         }));
      },

      async prepare() {
         this.feedback = '';
         const shop = Alpine.store('shop');
         let items;

         try {
            items = checkoutItems(shop.products, shop.quantities);
         } catch (error) {
            this.feedback = error.message;
            return;
         }

         if (!this.endpoint) {
            this.feedback = 'La solicitud está preparada, pero falta conectar la ruta POST del controlador.';
            return;
         }

         const destination = new URL(this.endpoint, window.location.origin);
         if (destination.origin !== window.location.origin) {
            this.feedback = 'El destino de compra debe pertenecer a esta aplicación.';
            return;
         }

         items.sort((a, b) => a.product_id - b.product_id);

         const signature = JSON.stringify({
            email: this.email,
            currency: shop.currency,
            items,
         });

         let attempt = read('refugio:checkout-attempt:v1', null);
         if (!attempt || attempt.signature !== signature || !attempt.key) {
            attempt = {
               signature,
               key: window.crypto.randomUUID(),
            };
            save('refugio:checkout-attempt:v1', attempt);
         }

         const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
         if (!csrfToken) {
            this.feedback = 'Falta el token CSRF de Laravel en la página.';
            return;
         }

         this.busy = true;
         try {
            const response = await fetch(destination, {
               method: 'POST',
               credentials: 'same-origin',
               headers: {
                  'Content-Type': 'application/json',
                  Accept: 'application/json',
                  'X-CSRF-TOKEN': csrfToken,
               },
               body: JSON.stringify({
                  email: this.email,
                  currency: shop.currency,
                  items,
                  idempotency_key: attempt.key,
               }),
            });

            if (response.status === 501) {
               this.feedback = 'El checkout todavía no está implementado en el servidor (HTTP 501).';
               return;
            }
            if (response.status === 422) {
               const data = await response.json();
               this.feedback = Object.values(data.errors ?? {}).flat().join(' ') || 'El servidor rechazó los datos.';
               return;
            }
            if (response.status === 419) {
               this.feedback = 'La sesión caducó. Recarga la página antes de continuar.';
               return;
            }
            if (!response.ok) {
               const data = await response.json().catch(() => ({}));
               throw new Error(data.message || 'El servidor no pudo procesar la solicitud.');
            }

            const data = await response.json();
            if (!data.data?.order_number) throw new Error('El servidor no devolvi� el folio del pre-pedido.');
            this.feedback = `Pre-pedido ${data.data.order_number} creado. Estado: pendiente. Total: ${shop.price(Number(data.data.grand_total), data.data.currency)}. No se realiz� ning�n pago.`;
         } catch (error) {
            this.feedback = error.message || 'No fue posible enviar la solicitud.';
         } finally {
            this.busy = false;
         }
      },
   }));

   Alpine.data('catalog', (options = {}) => ({
      query: '',
      category: '',
      categoryMenuOpen: false,
      categoryQuery: '',
      brand: '',
      stock: false,
      maximum: '',
      sort: '',
      list: read('refugio:catalog-list', false) === true,
      filtersOpen: !options.compact && window.innerWidth >= 1024,
      selected: null,
      imageErrors: {},

      get categories() {
         return [...Alpine.store('shop').categories].sort((a, b) => a.name.localeCompare(b.name, 'es', { numeric: true, sensitivity: 'base' }));
      },
      get selectedCategoryName() {
         return this.categories.find(item => item.slug === this.category)?.name || 'Todas las categorías';
      },
      get filteredCategories() {
         const normalize = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');
         const query = normalize(this.categoryQuery.trim());
         return this.categories.filter(item => normalize(item.name).includes(query));
      },
      toggleCategoryMenu() {
         this.categoryMenuOpen = !this.categoryMenuOpen;
         if (this.categoryMenuOpen) {
            this.categoryQuery = '';
            this.$nextTick(() => this.$refs.categorySearch.focus());
         }
      },
      closeCategoryMenu() {
         if (!this.categoryMenuOpen) return;
         this.categoryMenuOpen = false;
         this.$refs.categoryTrigger.focus();
      },
      chooseCategory(slug) {
         this.category = slug;
         this.closeCategoryMenu();
      },
      get brands() { return Alpine.store('shop').brands; },
      init() {
         void Alpine.store('shop').ensureProducts();
         void Alpine.store('shop').loadFilters();
         const group = new URLSearchParams(location.search).get('categoria');
         if (group) {
            const applyCategory = () => {
               const category = this.categories.find(item => item.slug === group || item.name === group);
               if (category) this.category = category.slug;
            };
            applyCategory();
            this.$watch('$store.shop.categories', applyCategory);
         }
         for (const key of ['category', 'brand', 'stock', 'sort']) this.$watch(key, () => this.applyFilters());
         this.$watch('$store.shop.currency', () => { this.maximum = ''; this.applyFilters(); });
      },
      applyFilters() {
         const shop = Alpine.store('shop');
         shop.query = this.query.trim();
         shop.filters = {
            category: this.category, brand: this.brand, stock: this.stock ? '1' : '',
            max_price: this.maximum, sort: ({ asc: 'price_asc', desc: 'price_desc' })[this.sort] || 'newest',
         };
         void shop.loadProducts(1);
      },
      get visible() { return Alpine.store('shop').products; },
      get activeFilters() {
         return [
            this.query && { key: 'query', label: 'Búsqueda: ' + this.query },
            this.category && { key: 'category', label: this.categories.find(item => item.slug === this.category)?.name || this.category },
            this.brand && { key: 'brand', label: this.brands.find(item => item.slug === this.brand)?.name || this.brand },
            this.stock && { key: 'stock', label: 'Disponibles' },
            this.maximum !== '' && {
               key: 'maximum',
               label: 'Hasta ' + Alpine.store('shop').price(Number(this.maximum)),
            },
         ].filter(Boolean);
      },
      imageUrl(product) {
         return this.imageErrors[product?.id] ? '' : externalProductImage(product);
      },
      imageError(product) {
         this.imageErrors = { ...this.imageErrors, [product.id]: true };
      },
      toggleList() {
         this.list = !this.list;
         save('refugio:catalog-list', this.list);
      },
      openProduct(product) {
         this.selected = product;
         this.$nextTick(() => this.$refs.quickview.showModal());
      },
      removeFilter(key) {
         this[key] = key === 'stock' ? false : '';
         if (key === 'query' || key === 'maximum') this.applyFilters();
      },
      reset() {
         this.query = '';
         this.category = '';
         this.brand = '';
         this.stock = false;
         this.maximum = '';
         this.sort = '';
         this.applyFilters();
      },
   }));

   Alpine.data('productPreview', () => ({
      init() { void Alpine.store('shop').ensureProducts(); },
      get product() {
         const id = decodeURIComponent(location.pathname.split('/').pop());
         return Alpine.store('shop').products.find(p => String(p.id) === id) ?? null;
      },
      quantity: 1,
      imageFailed: false,
      get imageUrl() {
         return this.imageFailed ? '' : externalProductImage(this.product);
      },
   }));

   Alpine.data('accountPreview', () => ({
      orders: [],
      addressOpen: false,
      feedback: 'Pendiente de conectar los datos de tu cuenta.',
   }));

   // Compatibilidad con las vistas existentes: sin registros inventados.
   // Las colecciones solo deben llenarse desde contratos reales del servidor.
   Alpine.data('adminPreview', (key = 'products') => ({
      key, query: '', status: '', page: 1, perPage: 10, lastPage: 1,
      from: '', until: '', feedback: 'Esta vista todavía no está conectada al backend.',
      datasets: {}, metrics: [], sales: [], top: [], selected: null, preview: {},
      get dataset() {
         return this.datasets[this.key] ?? {
            title: 'Sin datos del servidor',
            description: 'Pendiente de conexión al backend.',
            columns: [], rows: [],
         };
      },
      get filtered() { return this.dataset.rows; },
      get pages() { return this.lastPage; },
      get rows() { return this.dataset.rows; },
      get statuses() { return [...new Set(this.dataset.rows.map(row => row.status))]; },
      inspect(row) { this.selected = row; this.$refs.details.showModal(); },
      create() { this.selected = null; this.preview = {}; this.$refs.editor.showModal(); },
      edit(row) { this.selected = row; this.preview = {}; this.$refs.editor.showModal(); },
      submit() { this.feedback = 'Falta conectar el guardado al backend. No se guardaron cambios.'; },
      toggle() { this.feedback = 'Falta conectar el cambio de estado al backend.'; },
      remove(row) { this.selected = row; this.$refs.removal.showModal(); },
      confirmRemove() {
         this.feedback = 'Falta conectar la eliminación al backend. No se eliminó ningún registro.';
         this.$refs.removal.close();
      },
   }));
});
