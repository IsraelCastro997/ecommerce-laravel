import { filterCatalog, externalProductImage } from './catalog-presentation.js';
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

// // Los datos de dashboard y administración siguen siendo demostrativos.
// // Los productos del catálogo NO provienen de store.json.
// import demoStore from './demo/store.json';
// import demoTables from './demo/admin.json';

// Debe existir una ruta Laravel GET que responda: { "data": [productos] }.
// La respuesta debe cumplir el contrato de catalog-presentation.js y checkout-presentation.js.
const PRODUCTS_ENDPOINT = '/storefront/products';
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

    return { ...product, id: String(product.product_id) };
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

        init() {
            // Alpine ejecuta init() al registrar el store.
            void this.loadProducts();
        },

        async loadProducts(page = 1) {
            this.loading = true;
            this.error = '';

            try {
                const url = new URL(PRODUCTS_ENDPOINT, window.location.origin);
                url.searchParams.set('page', String(page));

                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error(`No se pudo consultar el catálogo de Laravel (HTTP ${response.status}).`);
                }

                const payload = await response.json();
                if (!payload || !Array.isArray(payload.data)) {
                    throw new Error('Laravel debe responder JSON con un arreglo en la propiedad data.');
                }
                if (payload.data.length > 100) {
                    throw new Error('El backend devolvió más de 100 productos en una página.');
                }

                const products = payload.data.map(normalizeProduct);
                this.products = products;

                // La paginación solo se muestra si el backend entrega metadatos.
                this.currentPage = Number.isSafeInteger(payload.current_page)
                    ? payload.current_page
                    : 1;
                this.lastPage = Number.isSafeInteger(payload.last_page)
                    ? payload.last_page
                    : 1;
            } catch (error) {
                this.error = error instanceof Error ? error.message : 'No se pudo cargar el catálogo.';
                this.products = [];
                console.error('Error al consultar el catálogo:', error);
            } finally {
                this.loading = false;
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
                if (!data.checkout_url) {
                    throw new Error('El servidor no devolvió la URL de pago.');
                }

                const checkoutUrl = new URL(data.checkout_url);
                if (
                    checkoutUrl.protocol !== 'https:' ||
                    checkoutUrl.hostname !== 'checkout.stripe.com'
                ) {
                    throw new Error('La dirección de pago recibida no es válida.');
                }
                window.location.assign(checkoutUrl.href);
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
        brand: '',
        stock: false,
        maximum: '',
        sort: '',
        list: read('refugio:catalog-list', false) === true,
        filtersOpen: !options.compact && window.innerWidth >= 1024,
        selected: null,
        imageErrors: {},

        get categories() {
            return [...new Set(Alpine.store('shop').products.map(p => p.category).filter(Boolean))];
        },
        get brands() {
            return [...new Set(Alpine.store('shop').products.map(p => p.brand).filter(Boolean))];
        },
        init() {
            const group = new URLSearchParams(location.search).get('categoria');
            if (this.categories.includes(group)) this.category = group;
            this.$watch('$store.shop.currency', () => { this.maximum = ''; });
        },
        get visible() {
            return filterCatalog(Alpine.store('shop').products, this, Alpine.store('shop').currency);
        },
        get activeFilters() {
            return [
                this.query && { key: 'query', label: 'Búsqueda: ' + this.query },
                this.category && { key: 'category', label: this.category },
                this.brand && { key: 'brand', label: this.brand },
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
        },
        reset() {
            this.query = '';
            this.category = '';
            this.brand = '';
            this.stock = false;
            this.maximum = '';
            this.sort = '';
        },
    }));

    Alpine.data('productPreview', () => ({
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
        orders: demoStore.dashboard.orders,
        addressOpen: false,
        feedback: '',
    }));

    Alpine.data('adminPreview', (key = 'products') => ({
        key,
        query: '',
        status: '',
        page: 1,
        perPage: 5,
        from: '',
        until: '',
        feedback: '',
        datasets: structuredClone(demoTables),
        metrics: demoStore.dashboard.metrics,
        sales: demoStore.dashboard.sales,
        top: demoStore.dashboard.topProducts,
        selected: null,
        preview: {},

        get dataset() {
            return this.datasets[this.key] ?? this.datasets.products;
        },
        get filtered() {
            return this.dataset.rows.filter(row =>
                (!this.query || row.cells.join(' ').toLocaleLowerCase('es-MX').includes(this.query.toLocaleLowerCase('es-MX'))) &&
                (!this.status || row.status === this.status) &&
                (!this.from || !row.occurredOn || row.occurredOn >= this.from) &&
                (!this.until || !row.occurredOn || row.occurredOn <= this.until)
            );
        },
        get pages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },
        get rows() {
            const page = Math.min(this.page, this.pages);
            return this.filtered.slice((page - 1) * this.perPage, page * this.perPage);
        },
        get statuses() {
            return [...new Set(this.dataset.rows.map(row => row.status))];
        },
        inspect(row) {
            this.selected = row;
            this.preview = { sku: row.cells[0], name: row.cells[1], mxn: '1899.00', usd: '109.00', stock: '32' };
            this.$refs.details.showModal();
        },
        create() {
            this.selected = null;
            this.preview = { sku: '', name: '', mxn: '', usd: '', stock: '0' };
            this.$refs.editor.showModal();
        },
        edit(row) {
            this.selected = row;
            this.preview = { sku: row.cells[0], name: row.cells[1], mxn: '1899.00', usd: '109.00', stock: '32' };
            this.$refs.editor.showModal();
        },
        submit() {
            this.feedback = 'Validación visual correcta. No se ha guardado en la base de datos.';
            this.$refs.editor.close();
        },
        toggle(row) {
            row.status = row.status === 'Inactivo · Demo' ? 'Activo · Demo' : 'Inactivo · Demo';
            this.feedback = 'Estado cambiado únicamente en esta vista de demostración.';
        },
        remove(row) {
            this.selected = row;
            this.$refs.removal.showModal();
        },
        confirmRemove() {
            this.datasets[this.key].rows = this.dataset.rows.filter(row => row.id !== this.selected.id);
            this.feedback = 'Fila retirada de la vista previa. La base de datos no cambió.';
            this.$refs.removal.close();
        },
    }));
});