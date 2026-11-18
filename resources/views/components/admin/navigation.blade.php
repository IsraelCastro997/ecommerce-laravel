<nav class="blade-admin-nav" aria-label="Administración">
 @foreach(['admin.dashboard'=>'Resumen','admin.products.index'=>'Productos','admin.categories.index'=>'Categorías','admin.brands.index'=>'Marcas','admin.attributes.index'=>'Atributos','admin.inventory.index'=>'Inventario','admin.warehouses.index'=>'Almacenes','admin.pricing.index'=>'Precios','admin.orders.index'=>'Pedidos','admin.payments.index'=>'Pagos','admin.customers.index'=>'Clientes','admin.roles.index'=>'Roles','admin.permissions.index'=>'Permisos','admin.reports.sales'=>'Ventas','admin.reports.top-products'=>'Top 3','admin.reports.inventory'=>'Reporte de inventario','admin.exchange-rates.index'=>'Divisas','admin.integrations.index'=>'Integraciones','admin.settings.index'=>'Configuración'] as $name=>$label)
 @if(Route::has($name))<a href="{{ route($name) }}" @if(request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>@endif
 @endforeach
</nav>
