import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import '../css/admin-dashboard.css';
import { dashboardEndpoints, fixture, demoResponse } from './dashboard-data.js';

const money = value => new Intl.NumberFormat('es-MX', {
 style: 'currency', currency: 'MXN', currencyDisplay: 'code', maximumFractionDigits: 0,
}).format(value);

document.addEventListener('alpine:init', () => {
 const dateInput = date => new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
 window.Alpine.data('refugioAnalytics', (mode = 'overview') => {
  let controller;
  let active = true;
  return {
   from: dateInput(new Date(Date.now() - 29 * 86400000)), until: dateInput(new Date()),
   currency: 'MXN', metric: 'sales', rawSales: [], summary: {}, top: [], loading: false, error: '',
   init() { this.refresh(); },
   money(value) { return new Intl.NumberFormat('es-MX', { style:'currency',currency:this.currency,currencyDisplay:'code' }).format(value); },
   get invalid() { return this.from && this.until && this.from > this.until; },
   get days() { return this.rawSales.filter(row => row.currency === this.currency).map(row => ({ date: row.sale_date, sales:Number(row.total), orders:Number(row.orders), profit:null })); },
   get sales() { return this.days.reduce((sum,row)=>sum+row.sales,0); },
   get orders() { return this.days.reduce((sum,row)=>sum+row.orders,0); },
   get maximum() { return Math.max(1,...this.days.map(row=>row.sales)); },
   dayLabel(date) { return new Intl.DateTimeFormat('es-MX',{day:'numeric',month:'short'}).format(new Date(date+'T12:00:00')); },
   reset() { this.from=dateInput(new Date(Date.now()-29*86400000));this.until=dateInput(new Date());this.refresh(); },
   async refresh() {
    if(this.invalid) return;
    controller?.abort();controller=new window.AbortController();
    const signal=controller.signal;
    this.loading=true;this.error='';
    const params=new URLSearchParams({from:this.from,until:this.until,currency:this.currency});
    const get=async path=>{
     const response=await fetch('/admin/dashboard/'+path+'?'+params,{credentials:'same-origin',headers:{Accept:'application/json'},signal});
     if(!response.ok) {const error=await response.json().catch(()=>({}));throw new Error(Object.values(error.errors??{}).flat().join(' ')||'No se pudo cargar el reporte. Revisa tu sesión e intenta de nuevo.');}
     return (await response.json()).data;
    };
    try {
     const [top,summary,sales]=await Promise.all([get('top-products'),mode==='top'?null:get('summary'),mode==='top'?null:get('sales')]);
     if(!active||signal.aborted)return;
     this.top=top.map(product=>({id:product.product_id,name:product.name,units:Number(product.units_sold)}));
     if(mode!=='top'){this.summary=summary;this.rawSales=sales;}
    }catch(error){if(active&&error.name!=='AbortError'){this.error=error.message;this.top=[];this.rawSales=[];}}
    finally{if(active&&!signal.aborted)this.loading=false;}
   },
   destroy(){active=false;controller?.abort();},
  };
 });

 window.Alpine.data('refugioDataTable', (kind = 'products') => {
  // Keep the library instance outside Alpine's reactive proxy.
  let table;
  let active = true;
  let controller;
  return {
   kind, category: '', status: '', query: '', loading: true, error: '',
   categories: [...new Set(fixture.products.data.map(row => row.category))],
   get statuses() { return [...new Set(fixture[this.kind].data.map(row => row.status))]; },
   init() {
    this.$nextTick(() => {
     if (!active) return;
     const text = DataTable.render.text();
     const columns = kind === 'products' ? [
      { data: 'sku', title: 'SKU', render: text },
      { data: 'name', title: 'Producto', render: text },
      { data: 'category', title: 'Categoría', render: text },
      { data: 'price', title: 'Precio MXN', render: (value, type) => type === 'display' ? money(Number(value)) : value },
      { data: 'stock', title: 'Stock', render: text },
      { data: 'status', title: 'Estado', render: text },
     ] : [
      { data: 'number', title: 'Pedido', render: text },
      { data: 'date', title: 'Fecha', render: text },
      { data: 'customer', title: 'Cliente de ejemplo', render: text },
      { data: 'items', title: 'Artículos', render: text },
      { data: 'total', title: 'Total MXN', render: (value, type) => type === 'display' ? money(Number(value)) : value },
      { data: 'status', title: 'Estado', render: text },
     ];
     table = new DataTable(this.$refs.table, {
      serverSide: true, processing: true, pageLength: 10, lengthChange: false,
      order: [[0, 'asc']], columns, searchDelay: 350,
      layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' },
      language: {
       emptyTable: 'No hay registros de ejemplo.', zeroRecords: 'No hay resultados con estos filtros.',
       info: '_START_–_END_ de _TOTAL_ registros', infoEmpty: '0 registros',
       infoFiltered: '(de _MAX_ en la muestra)', processing: 'Cargando registros…',
       paginate: { first: 'Primera', previous: 'Anterior', next: 'Siguiente', last: 'Última' },
       aria: { orderable: 'Ordenar por esta columna', orderableReverse: 'Invertir orden' },
      },
      ajax: async (request, callback) => {
       this.loading = true;
       this.error = '';
       try {
        let response;
        const filters = { category: this.category, status: this.status };
        const endpoint = dashboardEndpoints[kind];
        if (!endpoint) {
         // The fixture contains ONE page (< 10 rows). No client-side pagination.
         response = demoResponse(kind, request, filters);
        } else {
         controller?.abort();
         controller = new window.AbortController();
         const url = new URL(endpoint, window.location.origin);
         if (url.origin !== window.location.origin) throw new Error('El endpoint debe pertenecer a esta aplicación.');
         const append = (prefix, value) => {
          if (value && typeof value === 'object') Object.entries(value).forEach(([key, entry]) => append(`${prefix}[${key}]`, entry));
          else url.searchParams.set(prefix, String(value ?? ''));
         };
         Object.entries({ ...request, ...filters }).forEach(([key, value]) => append(key, value));
         const result = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: controller.signal });
         if (!result.ok) throw new Error('No se pudo cargar la tabla. Revisa tu sesión o vuelve a intentar.');
         response = await result.json();
         if (!Array.isArray(response.data) || response.data.length > request.length || !Number.isInteger(response.recordsTotal) || !Number.isInteger(response.recordsFiltered)) throw new Error('La API debe devolver una página y los contadores de DataTables.');
        }
        if (active) callback(response);
       } catch (error) {
        if (!active || error.name === 'AbortError') return;
        this.error = error.message;
        callback({ draw: Number(request.draw), recordsTotal: 0, recordsFiltered: 0, data: [] });
       } finally { if (active) this.loading = false; }
      },
     });
    });
   },
   refresh() { table?.search(this.query).draw(); },
   reset() { this.query = ''; this.category = ''; this.status = ''; this.refresh(); },
   destroy() { active = false; controller?.abort(); table?.destroy(); },
  };
 });
});
