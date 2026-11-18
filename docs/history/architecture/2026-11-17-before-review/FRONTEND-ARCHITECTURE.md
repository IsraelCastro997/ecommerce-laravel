# Frontend actual — 2026-11-17

- Laravel 12, Livewire 4, Blade, Alpine proporcionado por Livewire, Tailwind y Vite.
- Páginas completas mediante Route::livewire y clases existentes en app/Livewire. Layout predeterminado resources/views/layouts/app.blade.php; estructura común components/site-layout.blade.php.
- Autenticación: controladores nativos existentes, modificados exclusivamente para devolver View en lugar de Inertia Response. Guards, acciones, sesiones, hashing, throttling y CSRF se conservan.
- Estado visual y fixtures aislados: resources/js/app.js y resources/js/demo/*.json.
- Estimaciones y selección de IDs para checkout: resources/js/checkout-presentation.js. No actúan como autoridad de precios ni inventario.
- Administración: componentes reutilizables de navegación, tablas, formularios y gráficos; sin persistencia comercial. Las rutas protegidas conservan sus middleware.
- Catálogo: DTOs opcionales con product_id real; sin proveedor backend se muestran ejemplos, cuyos IDs nulos impiden un envío como productos reales.
- No existe una segunda aplicación React, SSR ni adaptador Inertia. Vite tiene una única entrada resources/js/app.js.
- La lógica de stock, reservas, precios definitivos, pedidos, permisos, Redis/API de divisas y cobros queda reservada al responsable por su instrucción posterior. No se ha creado un documento de servicios/stock.
