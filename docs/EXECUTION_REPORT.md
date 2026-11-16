# Informe de ejecución

El informe documenta el arranque inicial en el commit 5d746e5. La migración posterior a React y el estado actual se registran en [TRABAJO_REALIZADO.md](TRABAJO_REALIZADO.md).

## Entorno

- Windows con PHP 8.5.12, Composer 2.8.6, Node 26.10.0, npm 11.19.1 y Git 2.49.0.
- Starter oficial Vue/Inertia `v1.0.1`, Laravel 12.72.0, Inertia 2.0.28 y Vite 6.1.1. Livewire 4.4.8 y Spatie Laravel Permission 8.5.0 se instalaron con Composer. [Starter oficial versionado](https://github.com/laravel/vue-starter-kit/tree/v1.0.1)
- Laravel respondió en `http://127.0.0.1:8000` y Vite en `http://127.0.0.1:5173`. La sesión local usa SQLite en `database/database.sqlite`.

## Comandos de preparación

En el directorio padre se ejecutaron `composer create-project laravel/vue-starter-kit ecommerce-laravel v1.0.1 --prefer-dist --no-interaction --no-scripts`, `git init -b main ecommerce-laravel` y `Expand-Archive` sobre el ZIP de referencia. Dentro del proyecto se usaron estos comandos. La publicación de Spatie ya está incluida en el repositorio local.

```powershell
composer require 'livewire/livewire:4.*' 'spatie/laravel-permission:8.*' --no-scripts --no-progress --no-interaction
npm.cmd install --no-audit --no-fund
php artisan package:discover
php artisan key:generate
php artisan vendor:publish --provider='Spatie\Permission\PermissionServiceProvider' --tag=permission-migrations --no-interaction
php artisan vendor:publish --provider='Spatie\Permission\PermissionServiceProvider' --tag=permission-config --no-interaction
php artisan migrate --no-interaction
php artisan migrate:status
php artisan route:list --except-vendor
npm.cmd run build
```

Se crearon `.env` y SQLite en local. Los scripts de Composer se omitieron en el bootstrap para elegir explícitamente dónde migrar. Para reiniciar la aplicación existente, abrir dos terminales en `ecommerce-laravel`.

```powershell
php artisan serve --host=127.0.0.1 --port=8000 --tries=1
npm.cmd run dev -- --host 127.0.0.1 --port 5173 --strictPort
```

Detener cada proceso con Ctrl+C si se inició en su terminal. Los procesos de esta verificación se iniciaron en segundo plano. En la última verificación, el servidor HTTP de PHP escuchaba con PID 16524 y Vite con PID 3732. Los logs de desarrollo están en `storage/logs`. El HTML de Laravel y el cliente de Vite se verificaron por HTTP. Los dos servicios pueden reiniciarse con los comandos anteriores si la sesión de ejecución los termina.

## Migraciones y alcance

Se extrajo el contenido del ZIP en `../referencia-migraciones`. Se instalaron en la app sus seis migraciones con dos adaptaciones: `FULLTEXT` en catálogo se reserva a MySQL/MariaDB y la migración de identidad delega roles a Spatie. Las demás migraciones de referencia se copiaron como estaban. El archivo masivo permanece en la referencia extraída y el catálogo de verificación contiene cero productos reales.

Se confirmaron **10 migraciones** en SQLite local y **10 en MariaDB 10.11.16** en una instancia temporal aislada en `127.0.0.1:33317`. En MariaDB se verificó el índice `products_name_description_fulltext` y se apagó la instancia temporal. Quedan pendientes el rollback de aceptación, el seed de volumen y los benchmarks de 300 mil y 1 millón.

## Gates

| Gate | Estado | Evidencia |
|---|---|---|
| G0 · entorno | PASS | Inventario de documentación, ZIP, herramientas y versiones. |
| G1 · proyecto | PASS | Laravel 12, Vue/Inertia, Livewire y Spatie instalados; repo Git local. |
| G2 · esquema | PASS | 10 migraciones en cada uno de SQLite y MariaDB. La validación a escala queda pendiente. |
| G3 · auth y ejecución | PASS | HTTP 200 en inicio, login, registro y cliente Vite. Chromium comprobó invitado, creación de cuenta sintética, login y navegación a perfil. |
| G4 · tienda y cliente | PASS | Catálogo demo, filtros, carrito y checkout recorridos; responsive y navegación por teclado comprobados en los recorridos indicados. |
| G5 · administración | PARTIAL | Dashboard Vue de demostración y Livewire instalado. Acceso administrativo real, acciones y tablas se integran manualmente. |
| G6 · verificación | PASS | Compilación, comprobación frontend, revisión visual, auditoría por SHA-256 de 51 archivos originales del starter sin diferencias y de los dos archivos publicados por Spatie y entregado el handoff. |

Evidencia visual y listado de pantallas en [FRONTEND_COMPONENTS.md](FRONTEND_COMPONENTS.md). Los pendientes de autorización, datos, errores del servidor, tablas, integraciones y escala se documentan en [BACKEND_HANDOFF.md](BACKEND_HANDOFF.md).


La auditoría se hizo contra los ZIP oficiales de Composer y los archivos publicados del paquete. El historial del proyecto se puede consultar con git log -1. Git excluye .env, la base SQLite, las dependencias y el build.
