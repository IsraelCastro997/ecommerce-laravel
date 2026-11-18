<nav class="blade-admin-nav ro-navigation" aria-label="Administración" x-data="{ expanded: false, wide: window.innerWidth > 1000 }" @resize.window.debounce.150ms="wide = window.innerWidth > 1000" x-id="['admin-links']">
<div class="ro-nav-brand"><x-store-icon name="mountain" size="24" /><div><strong>Refugio</strong><small>Administración</small></div></div>
<button class="ro-nav-toggle" type="button" :aria-expanded="wide || expanded" :aria-controls="$id('admin-links')" @click="expanded = !expanded"><x-store-icon name="list" size="19" /><span>Menú de administración</span><x-store-icon name="chevron" size="14" /></button>
<div class="ro-nav-sections" :id="$id('admin-links')" x-show="wide || expanded" x-cloak>
@foreach([
'Tienda'=>['admin.dashboard'=>['Resumen','grid'],'admin.products.index'=>['Productos','bag'],'admin.categories.index'=>['Categorías','list'],'admin.brands.index'=>['Marcas','list'],'admin.inventory.index'=>['Inventario','list'],'admin.warehouses.index'=>['Almacenes','list'],'admin.pricing.index'=>['Precios','list']],
'Operación'=>['admin.orders.index'=>['Pedidos','bag'],'admin.payments.index'=>['Pagos','list'],'admin.customers.index'=>['Clientes','user']],
'Reportes'=>['admin.reports.sales'=>['Ventas','grid'],'admin.reports.top-products'=>['Top 3 de productos','grid'],'admin.reports.inventory'=>['Inventario','list']],
'Configuración'=>['admin.exchange-rates.index'=>['Divisas','list'],'admin.integrations.index'=>['Integraciones','grid'],'admin.roles.index'=>['Roles','user'],'admin.permissions.index'=>['Permisos','user'],'admin.settings.index'=>['Configuración','list']]
] as $group=>$links)
<section class="ro-nav-group"><h2>{{ $group }}</h2>@foreach($links as $name=>$item)@if(Route::has($name))<a href="{{ route($name) }}" @if(request()->routeIs($name)) aria-current="page" @endif><x-store-icon :name="$item[1]" size="17" /><span>{{ $item[0] }}</span></a>@endif@endforeach</section>
@endforeach
</div></nav>