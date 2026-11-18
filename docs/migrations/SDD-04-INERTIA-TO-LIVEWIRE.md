# SDD-04 — Migración del frontend

Fecha: 2026-11-17.

Alcance final: el responsable autorizó conservar Livewire 4 y ordenó limitar la implementación a vistas, gráficos, rutas y adaptación de presentación; modelos y migraciones continúan permitidos. La lógica comercial se retiró expresamente. El SDD integral original no se declara COMPLETE.

## Resultado

- React e Inertia retirados de Composer, npm y código ejecutable de la aplicación.
- 30 archivos obsoletos retirados: páginas, layouts, componentes, composables y tipos React/TypeScript; app.ts, vite.config.ts, tsconfig.json, plantilla Inertia y middleware exclusivo.
- Conservados CSS, Tailwind, assets e identidad visual de Refugio Outdoor.
- Completadas las vistas de las rutas de tienda/cuenta y de los componentes administrativos existentes. Las compras a proveedores permanecen fuera de alcance y sin rutas nuevas.
- Home creada con Artisan; layout generado mediante livewire:layout y adaptado a Blade.
- Rutas Livewire 4 mediante Route::livewire, sin el cambio a Route::get propuesto para la versión 3.
- Formularios nativos de autenticación y perfil adaptados a Blade.
- POST de checkout con transporte explícito 501; sin cálculo o persistencia de negocio en el controlador.
- Retirados CartService, CheckoutService, ExchangeRateService, CatalogData, Money y comandos comerciales recién generados; no se ejecutaron sus operaciones comerciales.
- Conservada y aplicada la migración incremental de available_from, available_until y customer_email; casts de fechas añadidos al modelo Product.
- Ajuste de la constante PDO de configuración para compatibilidad PHP 8.5 manteniendo compatibilidad anterior.
- Autoload regenerado mediante Composer sin optimización tras atascarse el barrido optimizado. No se modificó vendor manualmente.

## Datos y límites

MariaDB conserva 300000 productos. No se ejecutó fresh, refresh, wipe ni el seeder masivo. SQLite no se reintrodujo y el script post-create de Composer ya no crea ese archivo.

La autorización administrativa real no se implementó ni se eludió. Los tests de renderizado usan una autorización de fixture únicamente dentro de la prueba. El carrito y los gráficos son demostraciones frontend. La integración comercial, la provisión de IDs reales y Redis/API quedan a cargo del responsable.

No se crearon credenciales administrativas ni se hizo commit o push.
