# Refugio Outdoor

Prototipo de e-commerce con **Laravel 12, Livewire 4, Blade, Alpine.js, Tailwind CSS, Vite y MariaDB**. Incluye autenticación nativa, Google Identity Services, roles de Spatie, catálogo paginado y una integración de Stripe Checkout destinada a sandbox.

La evaluación original menciona Livewire 3; por decisión del responsable se conserva Livewire 4. React, Vue e Inertia fueron retirados. Livewire proporciona Alpine: no instalar ni iniciar una segunda instancia.

## Requisitos

- PHP **8.3 o superior**, compatible con `composer.lock`. El entorno local utilizado tiene PHP 8.5; Spatie 8 requiere PHP 8.3 aunque Laravel 12 admita 8.2.
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `ctype`, `dom`, `xml`, `tokenizer`, `bcmath` e `intl`. Composer comprobará los requisitos completos de las dependencias.
- Composer 2, Node.js 22 o superior y npm.
- MariaDB 10.11 o MySQL compatible. La configuración documentada usa el driver `mariadb`, no SQLite.
- Redis y su extensión PHP si se utiliza `REDIS_CLIENT=phpredis`.
- Elasticsearch para completar la búsqueda prevista; el catálogo actual utiliza SQL y puede arrancar sin ese servicio.
- Stripe CLI y acceso a un sandbox para recibir webhooks durante compras de prueba.

Todos los comandos se ejecutan desde **`ecommerce-laravel`**. Los ejemplos de terminal usan PowerShell.

## Instalación

### 1. Dependencias y archivo de entorno

Después de clonar/descomprimir el proyecto y entrar en su carpeta:

```powershell
composer install
composer check-platform-reqs
npm ci
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Editar `.env` con esta configuración base. La plantilla puede conservar valores iniciales de Laravel: sustituir especialmente `DB_CONNECTION=sqlite`.

```dotenv
APP_NAME="Refugio Outdoor"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_MX

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
MAIL_MAILER=log
MAIL_FROM_ADDRESS=demo@refugio.test
MAIL_FROM_NAME="Refugio Outdoor"
```

Ajustar usuario y contraseña a la base local. `root` sin contraseña corresponde únicamente al ejemplo local, no a producción.

Generar la clave **solo en una instalación nueva con `APP_KEY` vacío**:

```powershell
php artisan key:generate
```

No sobrescribir `.env` ni regenerar `APP_KEY` al arrancar una instalación existente. Después de editar variables:

```powershell
php artisan config:clear
```

### 2. Base de datos y migraciones

Crear la base desde la consola de MariaDB/MySQL o su administrador:

```sql
CREATE DATABASE IF NOT EXISTS ecommerce
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Aplicar las migraciones pendientes:

```powershell
php artisan migrate
```

**Con una base existente, conservar los datos.** No ejecutar `migrate:fresh`, `db:wipe` ni volver a sembrar el catálogo. El último conteo comprobado del entorno local fue de 300000 productos; esos registros no aparecen automáticamente al clonar el repositorio.

### 3. Dataset inicial: únicamente en una base nueva

En una base recién migrada y sin catálogo/datos auxiliares previos, configurar:

```dotenv
CATALOG_SEED_COUNT=300000
CATALOG_SEED_CHUNK=500
```

Después:

```powershell
php artisan db:seed --class=MillionProductSeeder
```

Este seeder carga productos sintéticos, categorías, marcas, monedas, mercado, almacén, precios publicados e inventario. Conserva también datos auxiliares del esquema histórico, sin que ello obligue a implementar esos módulos. La carga es grande y puede tardar.

Para generar un millón usar `CATALOG_SEED_COUNT=1000000` **antes de la primera carga**. El tamaño de lote permitido es 100–1000. El seeder solo admite entornos `local`/`testing`, exige `products` vacío y no sirve para ampliar una base ya poblada.

`php artisan db:seed` sin `--class` ejecuta `DatabaseSeeder`, que crea `test@example.com`: **no carga el catálogo ni las cuentas de evaluación de la siguiente sección**.

### 4. Compilar y arrancar

```powershell
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Abrir **http://localhost:8000**. Mantener el mismo hostname durante login, carrito y checkout. El servidor escucha en la interfaz local, pero el navegador utiliza `localhost` para coincidir con el origen autorizado de Google.

Durante desarrollo se puede mantener `npm run dev` en otra terminal. Si se utilizan jobs:

```powershell
php artisan queue:work
```

También existe `composer run dev`, que inicia servidor, cola, logs y Vite. Para revisar la entrega basta con los assets compilados y `artisan serve`.

## Variables de entorno de integraciones

| Variable | Valor esperado / finalidad |
|---|---|
| `GOOGLE_CLIENT_ID` | Client ID web de Google; necesario para el botón Google |
| `STRIPE_KEY` | `pk_test_...` del sandbox |
| `STRIPE_SECRET` | `sk_test_...` del mismo sandbox; únicamente en servidor |
| `STRIPE_WEBHOOK_SECRET` | `whsec_...` del listener/endpoint usado |
| `CASHIER_CURRENCY` | `mxn`; el checkout fija además la moneda del pedido |
| `CASHIER_CURRENCY_LOCALE` | `es_MX` |
| `CASHIER_LOGGER` | `stack` |
| `BANXICO_TOKEN` | Token privado de la API SIE |
| `BANXICO_API_URL` | `https://www.banxico.org.mx/SieAPIRest/service/v1` |
| `BANXICO_FIX_SERIES` | `SF43718`; comprobar su consumo efectivo en el servicio |
| `BANXICO_CACHE_TTL` | `1800` segundos, cuando la caché esté activa |
| `REDIS_CLIENT` | `phpredis`; requiere extensión PHP Redis |
| `REDIS_HOST`, `REDIS_PORT` | `127.0.0.1`, `6379` |
| `REDIS_PASSWORD` | `null` o contraseña del servidor local |
| `ELASTICSEARCH_HOST` | `https://127.0.0.1:9200`, según el servidor instalado |
| `ELASTICSEARCH_USERNAME` | `elastic` u otro usuario configurado |
| `ELASTICSEARCH_PASSWORD` | Contraseña del usuario Elasticsearch |
| `ELASTICSEARCH_CA_CERT` | Ruta absoluta al certificado CA para HTTPS |
| `ELASTICSEARCH_PRODUCTS_INDEX` | `ecommerce_products_v1` |
| `CATALOG_SEED_COUNT` | `300000` o `1000000`; solo para la carga inicial |
| `CATALOG_SEED_CHUNK` | `500`; tamaño de lote permitido 100–1000 |

Guardar claves reales en `.env`, nunca en este README. La configuración Google actual usa ID token y **no requiere Client Secret**. Registrar `http://localhost` y `http://localhost:8000` como orígenes JavaScript del cliente correspondiente. `127.0.0.1` es un origen distinto. [Configuración oficial de Google](https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid).

Se puede arrancar con `CACHE_STORE=database` y `QUEUE_CONNECTION=database`. Configurar Redis/Elasticsearch no activa por sí solo sus integraciones: la caché Banxico continúa comentada y la búsqueda revisada usa SQL.

`MAIL_MAILER=log` escribe mensajes localmente, sin entrega real. Para recibir recuperación de contraseña por correo, configurar SMTP: `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` y `MAIL_SCHEME` según el proveedor.

## Credenciales de prueba y permisos

Las siguientes cuentas existen en la base local de la entrega. Son **exclusivamente para evaluación local**, no para producción.

| Cuenta | Correo | Contraseña | Acceso |
|---|---|---|---|
| Administrador | admin.demo@refugio.test | `Refugio!c4c1e326212da1ac?` | Tienda, cuenta y `/admin` |
| Cliente | cliente.demo@refugio.test | `Refugio!c1003b2db83b0429?` | Tienda, cuenta y checkout; sin acceso administrativo |

Login de clientes: **http://localhost:8000/login**. Acceso administrativo exclusivo: **http://localhost:8000/admin/login**, sin registro público y con comprobación del permiso `access-admin` antes de crear la sesión. Registro público: **http://localhost:8000/register**. No se necesita ser administrador para registrarse o comprar.

### Recrear las cuentas en una base nueva

Clonar el código no copia los usuarios de la base local. Crear roles y permiso:

```powershell
php artisan permission:create-role admin web access-admin
php artisan permission:create-role customer web
php artisan tinker
```

Dentro de Tinker:

```php
$admin = App\Models\User::firstOrCreate(['email' => 'admin.demo@refugio.test'], ['name' => 'Administrador de pruebas', 'password' => 'Refugio!c4c1e326212da1ac?']);
$admin->forceFill(['email_verified_at' => now()])->save();
$admin->assignRole('admin');

$customer = App\Models\User::firstOrCreate(['email' => 'cliente.demo@refugio.test'], ['name' => 'Cliente de pruebas', 'password' => 'Refugio!c1003b2db83b0429?']);
$customer->forceFill(['email_verified_at' => now()])->save();
$customer->assignRole('customer');
exit
```

El cast de `User` genera el hash. Estos pasos se destinan a una base nueva: `firstOrCreate` conserva la contraseña si ese correo ya existe. Marcar el correo como verificado se limita a estas cuentas locales.

Al salir:

```powershell
php artisan permission:cache-reset
php artisan permission:show
```

Para otros usuarios existentes, sustituir `ID_USUARIO` por su ID real:

```powershell
php artisan permission:assign-role admin ID_USUARIO web
php artisan permission:assign-role customer ID_USUARIO web
```

`admin` tiene `access-admin`; `customer` no. Spatie resuelve ese permiso mediante `HasRoles` y el middleware `can:access-admin`. No quitar la protección para evitar un 403. [Comandos oficiales de Spatie](https://spatie.be/docs/laravel-permission/v8/basic-usage/artisan).

## Stripe en sandbox

Configurar las claves del **mismo sandbox**, sin utilizar claves live:

```dotenv
STRIPE_KEY=pk_test_REEMPLAZAR
STRIPE_SECRET=sk_test_REEMPLAZAR
STRIPE_WEBHOOK_SECRET=whsec_REEMPLAZAR
CASHIER_CURRENCY=mxn
CASHIER_CURRENCY_LOCALE=es_MX
CASHIER_LOGGER=stack
```

Con Stripe CLI instalada, ejecutar en otra terminal y autenticar el sandbox correspondiente:

```powershell
stripe login
stripe listen --events checkout.session.completed,checkout.session.expired --forward-to http://localhost:8000/stripe/webhook
```

Mantener el listener abierto y copiar su secreto `whsec_...` a `STRIPE_WEBHOOK_SECRET`. Puede diferir del secreto de un endpoint del Dashboard de Stripe. Después:

```powershell
php artisan config:clear
```

Cashier ya registra `/stripe/webhook`. No duplicar la ruta ni quitar CSRF de toda la aplicación. El formulario de checkout sí utiliza la sesión y CSRF de Laravel.

El formulario actual genera un pre-pedido pendiente y devuelve su folio; no inicia Stripe ni simula cobros. La implementación previa de pago se conserva separada, sin invocarse desde esta acción.

El controlador y webhook están implementados, pero esta documentación no acredita una compra completada. La bolsa sigue siendo local y necesita resolver artículos de otras páginas/persistencia completa para que cualquier recorrido funcione. [Stripe CLI](https://docs.stripe.com/cli) y [confirmación de pagos](https://docs.stripe.com/checkout/fulfillment).

## Rutas principales

Todas bajo `http://localhost:8000`:

| Ruta | Uso y acceso |
|---|---|
| `/` | Portada pública |
| `/catalogo` | Catálogo, búsqueda, filtros y paginación públicos |
| `/storefront/products?page=1` | Página JSON de productos |
| `/storefront/filters` | Categorías, marcas y monedas |
| `/productos/{product}` | Vista pública; detalle independiente aún por completar |
| `/login`, `/register` | Acceso y registro para invitados |
| `/dashboard`, `/cuenta` | Cuenta autenticada; `/dashboard` declara además `verified` |
| `/carrito` | Bolsa local pública |
| `/checkout` | Compra autenticada, sin permiso administrativo |
| `/checkout/success`, `/checkout/cancel` | Retorno de Stripe, autenticado |
| `/admin` | Dashboard: sesión y permiso `access-admin` |
| `/admin/products`, `/admin/orders` | Tablas administrativas protegidas |
| `/settings/profile` | Perfil autenticado |
| POST `/logout` | Cerrar sesión desde el botón del encabezado; sesión y CSRF |

## Estado y límites de la entrega

- Catálogo conectado a MariaDB, con paginación del servidor, precios MXN/USD, filtros por slugs e imágenes mediante URLs. Los nombres e imágenes sembrados son sintéticos.
- Autenticación nativa, registro, Google y cierre de sesión implementados; cuentas locales con roles diferenciados.
- Checkout y manejador Stripe presentes. Configuración/validación de cobros únicamente en sandbox.
- DataTables instalado en modo `serverSide: true`; endpoints administrativos todavía pendientes de conexión. Las gráficas conservan un JSON de ejemplo independiente de `app.js`.
- `DashboardController` contiene resumen, ventas y top 3; requiere ajustes e integración completa con las vistas.
- Cliente Elasticsearch y configuración instalados; búsqueda efectiva revisada todavía SQL. Redis y la integración de caché/conversión están en desarrollo.
- Los modelos/tablas históricos de proveedores, lotes y otros módulos ampliados no implican que se deban implementar en el prototipo principal.

## Comprobaciones opcionales

```powershell
npm run lint
npm run build
npm run test:frontend
```

PHPUnit requiere una base MariaDB **separada**, `ecommerce_frontend_testing`. Crear esa base antes de ejecutar:

```powershell
php artisan test --compact
```

`phpunit.xml` y `tests/TestCase.php` rechazan otra conexión/base de testing. No usar el catálogo persistente para fixtures destructivos. Esta actualización del README no ejecutó pruebas, migraciones ni seeds ni acredita que toda la suite histórica refleje los últimos cambios de backend.

## Documentación adicional

- [Historial del proyecto](docs/TRABAJO_REALIZADO.md)
- [Arquitectura del frontend](docs/architecture/FRONTEND-ARCHITECTURE.md)
- [Migración a Blade/Livewire](docs/migrations/SDD-04-INERTIA-TO-LIVEWIRE.md)
- [README original](docs/history/README_ORIGINAL.md)

Los antecedentes pueden describir versiones anteriores. Contrastar su estado con las rutas, controladores y consumidores actuales.

## Pre-pedidos

POST `/checkout` crea un pre-pedido pendiente, recalculado en servidor, sin iniciar pagos. Devuelve el folio, totales y líneas en `data`. Los reintentos con la misma clave conservan el pedido. La administración consulta `/admin/orders` mediante `/admin/dashboard/orders`, con paginación Laravel y permiso `access-admin`. La integración Stripe previa se conserva separada y no se invoca al generar el pre-pedido.

Acceso exclusivo del administrador: http://localhost:8000/admin/login. No tiene registro público y requiere el permiso `access-admin`.
