# Refugio Outdoor

Laravel 12 + Livewire 4 + Blade + Alpine.js + Tailwind CSS + Vite. React e Inertia fueron retirados. Por indicación posterior del responsable se conserva Livewire 4, aunque el documento original solicitaba la versión 3.

La entrega actual es frontend. Catálogo, bolsa, administración y gráficos usan datos de demostración claramente identificados. Login, registro, recuperación/restablecimiento, perfil, contraseña y logout conservan los controladores nativos existentes y ahora renderizan Blade. No hay servicios comerciales implementados por el agente.

## Inicio local

1. Ejecutar composer install y npm ci.
2. Configurar .env para MariaDB/MySQL local. No crear SQLite ni reiniciar la base existente. La base local conserva 300000 productos.
3. Ejecutar npm run build para usar assets compilados, o mantener npm run dev en otra terminal durante desarrollo.
4. Ejecutar php artisan serve --host=127.0.0.1 --port=8000.
5. Abrir http://127.0.0.1:8000.

La migración incremental de vigencia y correo del pedido ya se aplicó a la base local. No ejecutar el seeder masivo sobre un catálogo poblado.

## Pantallas

- Tienda: /, /catalogo, /productos/{product}, /carrito, /checkout, /pedido-confirmado/{order}.
- Cuenta autenticada: /dashboard, /cuenta, /cuenta/pedidos, /cuenta/pedidos/{order}, /cuenta/direcciones, /cuenta/moneda.
- Ajustes: /settings/profile, /settings/password, /settings/appearance.
- Autenticación: /login, /register, /forgot-password, /reset-password/{token}, /confirm-password, /verify-email.
- Administración: /admin y módulos de productos, categorías, marcas, atributos, inventario, almacenes, precios, pedidos, pagos, clientes, permisos, reportes, divisas, integraciones y configuración.

Las rutas administrativas conservan auth y can:access-admin. La configuración real de permisos sigue a cargo del responsable; no se abrió acceso para evitar un 403. El dashboard de cuenta conserva una demostración visual de administración separada, sin operaciones reales.

## Bolsa y formulario

La bolsa conserva cantidades y moneda en localStorage y muestra importes estimados e IVA del 16 %. El formulario tiene CSRF y envía IDs y cantidades, correo y moneda, sin precios ni totales. POST /checkout, nombre store.checkout.store, está conectado a CheckoutController::store, que responde HTTP 501 explícitamente hasta que el responsable implemente el backend. No crea pedidos ni cobra.

Los productos visuales de ejemplo no reciben IDs arbitrarios de MariaDB. El layout admite DTOs de catálogo mediante la propiedad catalogProducts; el frontend requiere product_id numérico real para enviar. La consulta y provisión de esos datos corresponde al backend humano.

Las imágenes existentes de diseño se preservaron. No se implementaron subidas ni descargas de imágenes de productos. DataTables queda sin integrar por decisión del responsable.

## Verificación

- npm run lint
- npm run test:frontend
- npm run build
- php artisan test --compact

PHPUnit usa exclusivamente MariaDB ecommerce_frontend_testing. Crear esa base separada antes de ejecutar las pruebas. Existe una guarda que rechaza ejecutar la suite con otra base; no utilizar la base persistente de 300000 productos para fixtures destructivos.

La verificación de navegador está en docs/testing/browser-sdd04.cjs; recibe SDD_PLAYWRIGHT_MODULE y opcionalmente SDD_BASE_URL. Debe apuntar a un servidor sobre la base separada de pruebas. No instalar una segunda instancia de Alpine: Livewire ya la proporciona.

## Documentación

- [Historial del proyecto](docs/TRABAJO_REALIZADO.md)
- [Migración del frontend](docs/migrations/SDD-04-INERTIA-TO-LIVEWIRE.md)
- [Arquitectura actual](docs/architecture/FRONTEND-ARCHITECTURE.md)
- [Verificación SDD-04](docs/testing/SDD-04-ACCEPTANCE.md)
- [README original](docs/history/README_ORIGINAL.md)

Los documentos SDD-02 y de bootstrap se conservan como antecedentes; las referencias históricas a Vue, React o Inertia no describen el stack actual.
