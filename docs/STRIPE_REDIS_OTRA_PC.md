# Stripe y Redis: otra PC y despliegue

Esta guía corresponde al código de Laravel 12, Livewire 4 y Alpine del repositorio. Todos los comandos se ejecutan en `ecommerce-laravel`, salvo la instalación global de Stripe CLI. Los ejemplos son para PowerShell.

## 1. Preparar la otra PC

Instalar Git, PHP compatible con `composer.lock`, Composer, Node.js/npm y MariaDB. Consultar también el [README](../README.md). Después de clonar:

```powershell
composer install
composer check-platform-reqs
npm ci
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Configurar MariaDB y las credenciales en `.env`. El repositorio no incluye la base de la otra PC: importar una copia de esa base si se quieren conservar productos, usuarios y pedidos. No usar `migrate:fresh` ni volver a ejecutar el seeder masivo sobre una base importada.

Conservar `APP_KEY` si se traslada la misma instalación y sus datos cifrados. En una instalación completamente nueva, con `APP_KEY` vacío, ejecutar `php artisan key:generate` una sola vez. Tras configurar la conexión:

```powershell
php artisan migrate
php artisan config:clear
npm run build
```

Usar un solo origen durante la prueba: por ejemplo `http://127.0.0.1:8000`, con ese mismo `APP_URL`. `localhost`, `127.0.0.1` y `tienda.test` son orígenes diferentes; Google OAuth debe autorizar el origen elegido. `.env` y las claves privadas se trasladan por un medio privado, no mediante commits.

## 2. Stripe: librería PHP y herramienta CLI

Cashier y `stripe/stripe-php` son dependencias del proyecto, instaladas mediante `composer install`. Crean la sesión de pago y verifican el resultado en el backend.

Stripe CLI es una herramienta independiente. Durante pruebas locales recibe eventos de Stripe y los reenvía a la computadora. No sustituye Cashier y no tiene que instalarse ni ejecutarse en el servidor de producción.

### Claves de sandbox

Crear una cuenta en [Stripe](https://dashboard.stripe.com/register), seleccionar el sandbox en el selector de cuenta y abrir [Claves API](https://dashboard.stripe.com/apikeys). Las claves y los eventos deben pertenecer al mismo sandbox.

```dotenv
APP_URL=http://127.0.0.1:8000
STRIPE_KEY=pk_test_REEMPLAZAR
STRIPE_SECRET=sk_test_REEMPLAZAR
STRIPE_WEBHOOK_SECRET=whsec_REEMPLAZAR
CASHIER_CURRENCY=mxn
CASHIER_CURRENCY_LOCALE=es_MX
CASHIER_LOGGER=stack
```

`STRIPE_WEBHOOK_SECRET` no es la clave pública ni la secreta API. Su origen depende de cómo se reciban los eventos. El flujo actual usa Checkout alojado por Stripe; el cobro se crea desde PHP, sin introducir tarjetas en Laravel.

### Pruebas con localhost: instalar y mantener abierta la CLI

```powershell
npm install --global @stripe/cli
```

Abrir una terminal nueva si `stripe` no se reconoce. Después:

```powershell
stripe --version
stripe login
stripe listen --events checkout.session.completed,checkout.session.async_payment_succeeded,checkout.session.expired --forward-to http://127.0.0.1:8000/stripe/webhook
```

El último comando muestra `Your webhook signing secret is whsec_...`. Copiar el valor completo a `STRIPE_WEBHOOK_SECRET`. Mantener esa terminal abierta durante la prueba. En otra terminal:

```powershell
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Si se prefiere Vite en desarrollo en vez del build, ejecutar `npm run dev` en otra terminal. No trasladar `public/hot` desde la otra PC: es una referencia temporal al servidor Vite de ese equipo. Si no se ejecutará Vite, retirar únicamente ese archivo temporal y usar `npm run build`.

### Recorrido de prueba

1. Iniciar sesión en la tienda y agregar un producto activo, vigente y con stock.
2. Entrar al checkout, revisar el correo y generar el pre-pedido.
3. El botón queda bloqueado después de crearlo. Para un pedido diferente se usa la acción explícita de crear otro.
4. Pulsar **Pagar con Stripe**. El servidor utiliza el total guardado, no un precio enviado desde JavaScript.
5. En Checkout usar la tarjeta de prueba `4242 4242 4242 4242`, fecha futura (por ejemplo `12/34`) y CVC `123`.
6. Volver a la tienda y esperar **Pago confirmado**. Revisar la entrega del webhook y los pedidos del dashboard.

Prueba de rechazo: tarjeta `4000 0000 0000 0002`. El pedido no debe marcarse pagado. Salir con el enlace de cancelar tampoco acredita ni rechaza un pago automáticamente. Al expirar una sesión, el webhook libera la reserva; cuando se paga, consume el stock una sola vez.

Las rutas relevantes son:

| Ruta | Uso |
| --- | --- |
| `POST /checkout` | Crear pre-pedido con precios, stock e IVA validados en Laravel |
| `POST /checkout/{order}/payment` | Crear o reutilizar sesión de Stripe; exige propietario autenticado |
| `GET /checkout/{order}/status` | Consultar estado autorizado |
| `POST /stripe/webhook` | Cashier verifica la firma y procesa la notificación |
| `GET /checkout/success?session_id=...` | Consultar pedido propio; la URL por sí misma no confirma pago |
| `GET /checkout/cancel?order=...` | Regresar y consultar o retomar el pedido |
| `GET /pedido-confirmado/{order}` | Detalle real del pedido, con actualización de estado |

Sin `STRIPE_SECRET` o `STRIPE_WEBHOOK_SECRET` el pago no se habilita. Una firma inválida no confirma pedidos. Las pruebas automatizadas usan un cliente Stripe simulado: no sustituyen una prueba manual completa en el sandbox.

### Deploy o prueba con URL pública: sin CLI

En [Webhooks de Stripe](https://dashboard.stripe.com/webhooks), dentro del entorno correspondiente, crear un endpoint con la URL pública HTTPS:

```text
https://tu-dominio.com/stripe/webhook
```

Seleccionar eventos snapshot `checkout.session.completed`, `checkout.session.async_payment_succeeded` y `checkout.session.expired`. Revelar el secreto de firma de ese endpoint y ponerlo en `STRIPE_WEBHOOK_SECRET` del servidor. Ese secreto es distinto del generado por `stripe listen`.

Actualizar `APP_URL` a la URL pública. Ejecutar `php artisan config:cache` después de configurar las variables. Stripe enviará los eventos directamente al servidor: no se ejecuta `stripe listen` en producción. Es posible desplegar la demo con claves sandbox; el cambio a cobros reales es una configuración distinta, con claves y endpoint del entorno live.

Cashier ya registra el webhook. No duplicar esa ruta ni desactivar CSRF globalmente. Stripe debe poder acceder al endpoint sin una sesión de usuario; el webhook se autentica mediante su firma.

## 3. Redis en la otra PC

Estado actual: Redis no está habilitado en el servicio de Banxico. En `app/Services/ExchangeRateService.php` tanto el import de `Cache` como el bloque `remember` están comentados. Instalar el servidor Redis o cambiar `.env` no descomenta ese código.

### Servidor Redis local mediante Docker

Con Docker Desktop instalado y ejecutándose:

```powershell
docker run --name refugio-redis -p 127.0.0.1:6379:6379 -d --restart unless-stopped redis:7-alpine
docker exec refugio-redis redis-cli ping
```

Debe responder `PONG`. Si el contenedor ya existe, usar `docker start refugio-redis` en lugar de crearlo otra vez. El servidor debe estar disponible mientras la aplicación use Redis. Esto es independiente de Stripe CLI.

### Cliente PHP

El servidor Redis no instala automáticamente un cliente PHP. Hay dos alternativas; elegir una:

- **PhpRedis:** instalar la extensión compatible con la versión, arquitectura y tipo TS/NTS del PHP que ejecuta Laravel. Comprobar con `php --ri redis` y usar `REDIS_CLIENT=phpredis`.
- **Predis:** alternativa en PHP puro para evitar la extensión nativa en Windows. No está incluido actualmente en `composer.lock`; instalarlo en la nueva PC con `composer require predis/predis` y usar `REDIS_CLIENT=predis`. Si se adopta para el proyecto, conservar los cambios de Composer en el repositorio.

Ejemplo con Predis y Docker:

```dotenv
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
REDIS_CACHE_DB=1
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
BANXICO_TOKEN=REEMPLAZAR
BANXICO_CACHE_TTL=1800
```

Mantener la caché general, sesiones y colas en database es compatible con usar explícitamente Redis solo para Banxico. Si se quiere usar Redis para la caché general, cambiar `CACHE_STORE=redis` después de verificar la conexión. No hace falta mover sesiones y colas para cachear el tipo de cambio.

### Activar la caché de Banxico cuando Redis funcione

En `app/Services/ExchangeRateService.php`, habilitar el import:

```php
use Illuminate\Support\Facades\Cache;
```

Reemplazar el cuerpo de `latestUsdMxn()` por:

```php
public function latestUsdMxn(): array
{
    return Cache::store('redis')->remember(
        'banxico:fix:usd-mxn',
        (int) config('services.banxico.cache_ttl', 1800),
        fn (): array => $this->fetchLatestUsdMxn()
    );
}
```

Retirar el `return $this->fetchLatestUsdMxn();` directo de ese método. Mantener el método privado de consulta. El TTL es en segundos: `1800` equivale a 30 minutos. El fallo de Redis no tiene actualmente fallback implementado; no habilitar este bloque antes de verificar el servicio y cliente.

```powershell
php artisan config:clear
php artisan tinker
```

En Tinker, verificar sin contactar Banxico:

```php
Illuminate\Support\Facades\Cache::store('redis')->put('refugio:check', 'ok', 60);
Illuminate\Support\Facades\Cache::store('redis')->get('refugio:check'); // "ok"
Illuminate\Support\Facades\Cache::store('redis')->forget('refugio:check');
```

Después, con token Banxico válido y el bloque habilitado, llamar dos veces a `app(App\Services\ExchangeRateService::class)->latestUsdMxn()`: la segunda llamada utiliza la caché mientras no expire el TTL. Este paso comprueba el servicio, no asegura que ya exista una pantalla consumiéndolo.

En producción Redis se ejecuta como servicio/contenedor o se usa un proveedor administrado. Laravel utiliza su cliente PHP; no se deja abierta una terminal `redis-cli`. Ajustar host, puerto y autenticación al servidor elegido.

## Fuentes

- [Instalar Stripe CLI](https://docs.stripe.com/cli/install)
- [Instalación oficial mediante npm](https://github.com/stripe/stripe-cli/wiki/Installation)
- [Claves API de Stripe](https://docs.stripe.com/keys)
- [Webhooks y reenvío local](https://docs.stripe.com/webhooks)
- [Tarjetas de prueba](https://docs.stripe.com/testing)
- [Redis con Docker](https://redis.io/docs/latest/operate/oss_and_stack/install/install-stack/docker/)
- [Clientes Redis de Laravel 12](https://laravel.com/docs/12.x/redis)
