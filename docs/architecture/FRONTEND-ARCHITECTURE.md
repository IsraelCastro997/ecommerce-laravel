# Frontend actual — 2026-11-17

- Laravel 12, Livewire 4, Blade, Alpine proporcionado por Livewire, Tailwind y Vite.
- Páginas completas mediante Route::livewire y clases existentes en app/Livewire. Layout predeterminado resources/views/layouts/app.blade.php; estructura común components/site-layout.blade.php.
- Autenticación: controladores nativos existentes, modificados exclusivamente para devolver View en lugar de Inertia Response. Guards, acciones, sesiones, hashing, throttling y CSRF se conservan.
- Estado visual y fixtures aislados: resources/js/app.js y resources/js/demo/*.json.
- Estimaciones y selección de IDs para checkout: resources/js/checkout-presentation.js. No actúan como autoridad de precios ni inventario.
- Administración: componentes reutilizables de navegación, tablas, formularios y gráficos; sin persistencia comercial. Las rutas protegidas conservan sus middleware.
- Catálogo: DTOs opcionales con product_id real; sin proveedor backend se muestran ejemplos, cuyos IDs nulos impiden un envío como productos reales.
- No existe una segunda aplicación React, SSR ni adaptador Inertia. Vite tiene una única entrada resources/js/app.js.
- La lógica de stock, reservas, precios definitivos, pedidos, permisos y cobros queda reservada al responsable. Las búsquedas usarán Elasticsearch y las descripciones tendrán caché Redis, manteniendo MariaDB como fuente original. Es arquitectura objetivo, no integración implementada; véase [búsqueda y caché](SEARCH_AND_CACHE.md).
- El frontend no consulta Elasticsearch ni Redis directamente: recibe páginas de DTOs desde Laravel y conserva IDs/cantidades para las solicitudes de compra. La paginación visual deberá conectarse al paginador del servidor.
- El catálogo fue rediseñado con filtros plegables, vista rápida, lista/cuadrícula, imágenes externas y fuentes locales. La revisión de pendientes está en docs/reviews/FRONTEND_AUDIT_2026-11-17.md.
- Cashier 16.11.0 y stripe-php 21.3.3 están instalados por el responsable. El controlador de checkout cambió después del prototipo 501, pero sigue incompleto: contiene referencias a variables todavía sin definir. El frontend aún necesita abrir la checkout_url devuelta por el servidor. No se presenta el cobro como operativo.
