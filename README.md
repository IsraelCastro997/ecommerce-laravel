# Refugio Outdoor

Prototipo de e-commerce Laravel 12. La tienda y la cuenta usan React con Inertia. Livewire 4 está instalado y los componentes de presentación de la futura administración están en `resources/views/components/admin`. La administración accesible hoy es la demostración dentro de `/dashboard`.

## Desarrollo local

1. Usa las dependencias bloqueadas en `composer.lock` y `package-lock.json`. Ejecuta `composer install` y `npm ci`.
2. Si es una instalación nueva, crea `.env` a partir de `.env.example`, genera la clave local y prepara una base de desarrollo. SQLite sirve para revisar el prototipo. MariaDB es el objetivo del esquema. El responsable aplica las migraciones a la base destinada al desarrollo.
3. En dos terminales ejecuta `php artisan serve --host=127.0.0.1 --port=8000` y `npm run dev -- --host 127.0.0.1 --port 5173`.
4. Abre [la tienda local](http://127.0.0.1:8000) o [el dashboard](http://127.0.0.1:8000/dashboard). Puedes registrar una cuenta sintética para ver el prototipo; el proyecto todavía no provisiona un administrador.

El checkout de demostración conserva el pago deshabilitado. El carrito guarda SKU y cantidades de muestra en este navegador. El repositorio excluye `.env` y la base SQLite local.

## Documentación

- [Historial completo del proyecto](docs/TRABAJO_REALIZADO.md)
- [Resultado de SDD-02 y matriz del enunciado](docs/iterations/SDD-02-RESULT.md)
- [Contrato y pendientes de servidor](docs/contracts/BACKEND_CONTRACTS.md)
- [Verificación de SDD-02](docs/testing/SDD-02-TESTS.md)

## Documentos de planificación

- [Instrucciones vigentes para agentes](AGENTS.md)
- [Plan inicial de bootstrap](AGENTS_FRONTEND_BOOTSTRAP.md)
- [Especificación inicial del frontend](SDD_FRONTEND_BOOTSTRAP_ECOMMERCE.md)
- [README original de planificación](docs/history/README_ORIGINAL.md)

El README original de planificación está fechado el 14 de noviembre de 2026 y describe el planteamiento inicial con Vue. El frontend actual utiliza React con Inertia y Livewire 4. Los planes de bootstrap se conservan como antecedentes. El historial de trabajo documenta las decisiones posteriores y la excepción autorizada para crear modelos Eloquent nuevos.
