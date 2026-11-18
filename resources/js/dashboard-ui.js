import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import '../css/admin-dashboard.css';
import { dashboardEndpoints, fixture, demoResponse } from './dashboard-data.js';

const money = value => new Intl.NumberFormat('es-MX', {
 style: 'currency', currency: 'MXN', currencyDisplay: 'code', maximumFractionDigits: 0,
}).format(value);

document.addEventListener('alpine:init', () => {
 window.Alpine.data('refugioOrders', () => {
  let controller;
  return {
   orders:[],page:1,last:1,from:'',until:'',loading:false,error:'',
   init(){ this.load(); },
   reset(){this.from='';this.until='';this.load();},
   async load(number=1){
    if(this.from && this.until && this.from>this.until){this.error='La fecha inicial debe ser anterior o igual a la final.';return;}
    controller?.abort();controller=new window.AbortController();const signal=controller.signal;
    this.loading=true;this.error='';
    const params=new URLSearchParams({page:number});
    if(this.from)params.set('from',this.from);
    if(this.until)params.set('until',this.until);
    try{
     const response=await fetch('/admin/dashboard/orders?'+params,{headers:{Accept:'application/json'},signal});
     const data=await response.json();
     if(!response.ok)throw new Error(Object.values(data.errors??{}).flat().join(' ')||'No se pudieron cargar los pedidos.');
     if(signal.aborted)return;
     this.orders=data.data;this.page=data.current_page;this.last=data.last_page;
    }catch(error){if(error.name!=='AbortError')this.error=error.message;}
    finally{if(!signal.aborted)this.loading=false;}
   },
   date(value){return new Intl.DateTimeFormat('es-MX',{dateStyle:'medium',timeStyle:'short'}).format(new Date(value));},
   destroy(){controller?.abort();},
  };
 });
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
   get days() { return this.rawSales.filter(row => row.currency === this.currency).map(row => ({ date: row.sale_date, sales:Number(row.total), orders:Number(row.orders), profit:row.profit == null ? null : Number(row.profit), missingCosts:Number(row.orders_without_cost ?? 0) })); },
   get sales() { return this.days.reduce((sum,row)=>sum+row.sales,0); },
   get orders() { return this.days.reduce((sum,row)=>sum+row.orders,0); },
   get profit() { return this.days.some(row=>row.profit === null) ? null : this.days.reduce((sum,row)=>sum+row.profit,0); },
   get missingCosts() { return this.days.reduce((sum,row)=>sum+row.missingCosts,0); },
   get maximum() { return Math.max(1,...this.days.map(row=>Math.abs(row[this.metric] ?? 0))); },
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
   kind, category: kind === 'products' ? (new URLSearchParams(window.location.search).get('category') || '') : '', status: '', stockFilter: '', query: '', notice: '', loading: true, error: '',
   categories: [...new Set(fixture.products.data.map(row => row.category))],
   get statuses() { return kind === 'products' ? ['draft','active','inactive'] : [...new Set(fixture[this.kind].data.map(row => row.status))]; },
   init() {
    this.$nextTick(() => {
     if (!active) return;
     const text = DataTable.render.text();
     const columns = kind === 'products' ? [
      { data: 'sku', title: 'SKU', render: text },
      { data: 'name', title: 'Producto', render: text },
      { data: 'category', title: 'Categoría', orderable: false, render: text },
      { data: 'price', title: 'Precio MXN', orderable: false, render: (value, type) => type === 'display' ? (value == null ? 'Sin precio' : money(Number(value))) : value },
      { data: 'stock', title: 'Stock', orderable: false, render: text },
      { data: 'status', title: 'Estado', render: (value,type,row) => {
       if(type !== 'display') return value;
       const on = value === 'active';
       return `<button type="button" class="product-switch" role="switch" aria-checked="${on}" aria-label="Activar producto ${Number(row.id)}" data-action="status" data-id="${Number(row.id)}" data-next="${on ? 'inactive' : 'active'}"><span class="product-switch-track"><span></span></span><span>${on ? 'Activo' : value === 'draft' ? 'Borrador' : 'Inactivo'}</span></button>`;
      } },
      { data: 'id', title: 'Acciones', orderable: false, searchable: false, render: (value, type) => {
       if (type !== 'display') return value;
       const id = Number(value);
       return `<div class="product-row-actions"><a href="/admin/products/${id}" title="Ver detalle">Detalle</a><a href="/admin/products/${id}/edit" title="Editar producto">Editar</a><button type="button" class="product-delete" data-action="delete" data-id="${id}">Eliminar</button></div>`;
      } },
     ] : [
      { data: 'number', title: 'Pedido', render: text },
      { data: 'date', title: 'Fecha', render: text },
      { data: 'customer', title: 'Cliente de ejemplo', render: text },
      { data: 'items', title: 'Artículos', render: text },
      { data: 'total', title: 'Total MXN', render: (value, type) => type === 'display' ? (value == null ? 'Sin precio' : money(Number(value))) : value },
      { data: 'status', title: 'Estado', render: text },
     ];
     table = new DataTable(this.$refs.table, {
      serverSide: true, processing: true, pageLength: 10, lengthChange: false,
      order: [[0, 'asc']], columns, searchDelay: 350,
      layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' },
      language: {
       emptyTable: 'No hay registros.', zeroRecords: 'No hay resultados con estos filtros.',
       info: '_START_–_END_ de _TOTAL_ registros', infoEmpty: '0 registros',
       infoFiltered: '(de _MAX_ registros)', processing: 'Cargando registros…',
       paginate: { first: 'Primera', previous: 'Anterior', next: 'Siguiente', last: 'Última' },
       aria: { orderable: 'Ordenar por esta columna', orderableReverse: 'Invertir orden' },
      },
      ajax: async (request, callback) => {
       this.loading = true;
       this.error = '';
       try {
        let response;
        const filters = { category: this.category, status: this.status, stock_filter: this.stockFilter };
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
   quickStatus(value) {this.status=value;this.refresh();},
   async handleAction(event) {
    const button=event.target.closest('button[data-action]');
    if(!button || button.disabled) return;
    const id=Number(button.dataset.id);
    const deleting=button.dataset.action==='delete';
    if(deleting && !window.confirm('¿Eliminar este producto del catálogo? Se conservará mediante eliminación lógica.')) return;
    button.disabled=true;this.error='';this.notice='';
    try {
     const response=await fetch('/admin/products/'+id+(deleting?'':'/status'),{
      method:deleting?'DELETE':'PATCH',credentials:'same-origin',
      headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
      body:JSON.stringify(deleting?{}:{status:button.dataset.next}),
     });
     const data=await response.json();
     if(!response.ok)throw new Error(Object.values(data.errors??{}).flat().join(' ')||data.message||'No se pudo actualizar el producto.');
     this.notice=deleting?'Producto eliminado.':data.data.status==='active'?'Producto activado.':'Producto desactivado.';
     table.ajax.reload(null,false);
    }catch(error){this.error=error.message;}
    finally{button.disabled=false;}
   },
   refresh() { table?.search(this.query).draw(); },
   reset() { this.query = ''; this.category = ''; this.status = ''; this.stockFilter = ''; this.refresh(); },
   destroy() { active = false; controller?.abort(); table?.destroy(); },
  };
 });
});
