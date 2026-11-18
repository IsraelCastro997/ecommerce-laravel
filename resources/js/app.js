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
// Alpine is supplied once by Livewire. Client totals are estimates, never payment authority.
document.addEventListener('alpine:init', () => {
 window.Alpine.data('themePicker', () => ({
  theme: 'light',
  init() { try { this.theme = localStorage.getItem('refugio:theme') ?? 'light'; } catch { /* Default. */ } this.apply(); },
  apply() { document.documentElement.classList.toggle('dark', this.theme === 'dark'); },
  set(value) { this.theme = value; this.apply(); try { localStorage.setItem('refugio:theme', value); } catch { /* In memory. */ } },
 }));
});

import store from './demo/store.json';
import tables from './demo/admin.json';
// Optional frontend DTOs supplied by the human-owned backend. Never invent database IDs.
const suppliedCatalog = document.getElementById('storefront-products');
if (suppliedCatalog) {
 try {
  const rows = JSON.parse(suppliedCatalog.textContent);
  if (!Array.isArray(rows) || rows.length > 100) throw new Error('El frontend requiere una página de hasta 100 productos.');
  store.products = rows.map(product => {
   if (!Number.isSafeInteger(product.product_id) || product.product_id < 1 || typeof product.name !== 'string') throw new Error('Contrato de producto inválido.');
   estimate([product], {}, 'MXN'); estimate([product], {}, 'USD');
   return { ...product, id: String(product.product_id) };
  });
 } catch (error) { console.error('No se pudo cargar el catálogo del servidor:', error.message); }
}
const read = (key, fallback) => { try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch { return fallback; } };
const save = (key, value) => { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* Works in memory. */ } };
document.addEventListener('alpine:init', () => {
 const Alpine = window.Alpine;
 let currency = 'MXN';
 try { currency = localStorage.getItem('refugio:display-currency') === 'USD' ? 'USD' : 'MXN'; } catch { /* Default. */ }
 const saved = read('refugio:demo-cart:v1', {});
 const quantities = Object.fromEntries(store.products.filter(p => p.available).flatMap(p => {
  const quantity = saved && typeof saved === 'object' ? saved[p.id] : 0;
  return Number.isInteger(quantity) && quantity > 0 ? [[p.id, Math.min(9, quantity)]] : [];
 }));
 Alpine.store('shop', {
  currency, quantities, products: store.products, notice: '',
  get count() { return Object.values(this.quantities).reduce((sum, n) => sum + n, 0); },
  get items() { return this.products.filter(p => this.quantities[p.id]); },
  get totals() { return estimate(this.products, this.quantities, this.currency); },
  get total() { return this.totals.subtotal; },
  estimateFor(currency) { return estimate(this.products, this.quantities, currency); },
  price(amount, currency = this.currency) { return new Intl.NumberFormat('es-MX', { style: 'currency', currency, currencyDisplay: 'code' }).format(amount); },
  setCurrency(value) { this.currency = value === 'USD' ? 'USD' : 'MXN'; try { localStorage.setItem('refugio:display-currency', this.currency); } catch { /* In memory. */ } },
  change(id, delta) {
   const product = this.products.find(p => p.id === id && p.available); if (!product) return;
   const next = { ...this.quantities }; const quantity = delta === 'remove' ? 0 : Math.max(0, Math.min(9, (next[id] ?? 0) + delta));
   if (quantity) next[id] = quantity; else delete next[id]; this.quantities = next; save('refugio:demo-cart:v1', next);
   this.notice = quantity ? product.name + ': ' + quantity + ' en la bolsa de demostración.' : 'Artículo retirado de la bolsa de demostración.';
  },
  clear() { this.quantities = {}; save('refugio:demo-cart:v1', {}); this.notice = 'Bolsa de demostración vaciada.'; },
 });
 Alpine.data('checkoutPreview', (endpoint = '') => ({
  endpoint,
  email: '', feedback: '', busy: false,
  get previewItems() { return Alpine.store('shop').items.map(product => ({ product_id: product.product_id ?? null, quantity: Alpine.store('shop').quantities[product.id] })); },
  async prepare() {
   this.feedback = '';
   const shop = Alpine.store('shop');
   let items;
   try { items = checkoutItems(shop.products, shop.quantities); } catch (error) { this.feedback = error.message; return; }
   if (!this.endpoint) { this.feedback = 'La solicitud está preparada, pero falta conectar la ruta POST del controlador.'; return; }
   const destination = new URL(this.endpoint, window.location.origin);
   if (destination.origin !== window.location.origin) { this.feedback = 'El destino de compra debe pertenecer a esta aplicación.'; return; }
   items.sort((a, b) => a.product_id - b.product_id);

const signature = JSON.stringify({
 email: this.email,
 currency: shop.currency,
 items,
});

let attempt;

try {
 attempt = JSON.parse(
  localStorage.getItem('refugio:checkout-attempt:v1') || 'null'
 );
} catch {
 attempt = null;
}

if (!attempt || attempt.signature !== signature || !attempt.key) {
 attempt = {
  signature,
  key: window.crypto.randomUUID(),
 };

 localStorage.setItem(
  'refugio:checkout-attempt:v1',
  JSON.stringify(attempt)
 );
}
   this.busy = true;
   try {
    const response = await fetch(destination, {
     method: 'POST', credentials: 'same-origin',
     headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
     body: JSON.stringify({
        email: this.email,
        currency: shop.currency,
        items,
        idempotency_key: attempt.key,
       }),
    });
    if (response.status === 422) {
     const data = await response.json(); this.feedback = Object.values(data.errors ?? {}).flat().join(' ') || 'El servidor rechazó los datos.'; return;
    }
    if (response.status === 419) { this.feedback = 'La sesión caducó. Recarga la página antes de continuar.'; return; }
    if (!response.ok) { const data = await response.json().catch(() => ({})); throw new Error(data.message || 'El servidor no pudo procesar la solicitud.'); }
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
   } catch (error) { this.feedback = error.message || 'No fue posible enviar la solicitud.'; }
   finally { this.busy = false; }
  },
 }));
 Alpine.data('catalog', (options = {}) => ({
  query: '', category: '', brand: '', stock: false, maximum: '', sort: '',
  list: read('refugio:catalog-list', false) === true,
  filtersOpen: !options.compact && window.innerWidth >= 1024,
  selected: null, imageErrors: {},
  get categories() { return [...new Set(store.products.map(p => p.category).filter(Boolean))]; },
  get brands() { return [...new Set(store.products.map(p => p.brand).filter(Boolean))]; },
  init() {
   const group = new URLSearchParams(location.search).get('categoria');
   if (this.categories.includes(group)) this.category = group;
   this.$watch('$store.shop.currency', () => { this.maximum = ''; });
  },
  get visible() { return filterCatalog(store.products, this, Alpine.store('shop').currency); },
  get activeFilters() {
   return [
    this.query && { key: 'query', label: 'Búsqueda: ' + this.query },
    this.category && { key: 'category', label: this.category },
    this.brand && { key: 'brand', label: this.brand },
    this.stock && { key: 'stock', label: 'Disponibles' },
    this.maximum !== '' && { key: 'maximum', label: 'Hasta ' + Alpine.store('shop').price(Number(this.maximum)) },
   ].filter(Boolean);
  },
  imageUrl(product) { return this.imageErrors[product?.id] ? '' : externalProductImage(product); },
  imageError(product) { this.imageErrors = { ...this.imageErrors, [product.id]: true }; },
  toggleList() { this.list = !this.list; save('refugio:catalog-list', this.list); },
  openProduct(product) { this.selected = product; this.$nextTick(() => this.$refs.quickview.showModal()); },
  removeFilter(key) { this[key] = key === 'stock' ? false : ''; },
  reset() { this.query = ''; this.category = ''; this.brand = ''; this.stock = false; this.maximum = ''; this.sort = ''; },
 }));
 Alpine.data('productPreview', () => ({
  product: store.products.find(p => String(p.id) === decodeURIComponent(location.pathname.split('/').pop())) ?? null,
  quantity: 1, imageFailed: false,
  get imageUrl() { return this.imageFailed ? '' : externalProductImage(this.product); },
 }));
 Alpine.data('accountPreview', () => ({ orders: store.dashboard.orders, addressOpen: false, feedback: '' }));
 Alpine.data('adminPreview', (key = 'products') => ({
  key, query: '', status: '', page: 1, perPage: 5, from: '', until: '', feedback: '',
  datasets: structuredClone(tables), metrics: store.dashboard.metrics, sales: store.dashboard.sales, top: store.dashboard.topProducts,
  selected: null, preview: {},
  get dataset() { return this.datasets[this.key] ?? this.datasets.products; },
  get filtered() { return this.dataset.rows.filter(r => (!this.query || r.cells.join(' ').toLocaleLowerCase('es-MX').includes(this.query.toLocaleLowerCase('es-MX'))) && (!this.status || r.status === this.status) && (!this.from || !r.occurredOn || r.occurredOn >= this.from) && (!this.until || !r.occurredOn || r.occurredOn <= this.until)); },
  get pages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
  get rows() { const page = Math.min(this.page,this.pages); return this.filtered.slice((page-1)*this.perPage, page*this.perPage); },
  get statuses() { return [...new Set(this.dataset.rows.map(r => r.status))]; },
  inspect(row) { this.selected = row; this.preview = { sku: row.cells[0], name: row.cells[1], mxn: '1899.00', usd: '109.00', stock: '32' }; this.$refs.details.showModal(); },
  create() { this.selected = null; this.preview = { sku: '', name: '', mxn: '', usd: '', stock: '0' }; this.$refs.editor.showModal(); },
  edit(row) { this.selected = row; this.preview = { sku: row.cells[0], name: row.cells[1], mxn: '1899.00', usd: '109.00', stock: '32' }; this.$refs.editor.showModal(); },
  submit() { this.feedback = 'Validación visual correcta. No se ha guardado en la base de datos.'; this.$refs.editor.close(); },
  toggle(row) { row.status = row.status === 'Inactivo · Demo' ? 'Activo · Demo' : 'Inactivo · Demo'; this.feedback = 'Estado cambiado únicamente en esta vista de demostración.'; },
  remove(row) { this.selected = row; this.$refs.removal.showModal(); },
  confirmRemove() { this.datasets[this.key].rows = this.dataset.rows.filter(r => r.id !== this.selected.id); this.feedback = 'Fila retirada de la vista previa. La base de datos no cambió.'; this.$refs.removal.close(); },
 }));
});
