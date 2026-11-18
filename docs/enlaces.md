# Documentación técnica — E-commerce Laravel 12

Repositorio de banxico

https://github.com/Xint0/banxico-php

## 1. Laravel 12

Framework PHP utilizado para el backend, rutas, controladores, modelos, validaciones y lógica de negocio.

- Documentación: https://laravel.com/docs/12.x
- Rutas: https://laravel.com/docs/12.x/routing
- Controladores: https://laravel.com/docs/12.x/controllers
- Eloquent ORM: https://laravel.com/docs/12.x/eloquent
- Relaciones Eloquent: https://laravel.com/docs/12.x/eloquent-relationships
- Migraciones: https://laravel.com/docs/12.x/migrations
- Query Builder: https://laravel.com/docs/12.x/queries
- Validaciones: https://laravel.com/docs/12.x/validation
- Blade: https://laravel.com/docs/12.x/blade
- Pruebas: https://laravel.com/docs/12.x/testing

## 2. Livewire 3

Framework para desarrollar interfaces dinámicas mediante componentes PHP y plantillas Blade, sin necesidad de React o Vue.

- Documentación: https://livewire.laravel.com/docs/3.x/quickstart
- Instalación: https://livewire.laravel.com/docs/3.x/installation
- Componentes: https://livewire.laravel.com/docs/3.x/components
- Formularios: https://livewire.laravel.com/docs/3.x/forms
- Propiedades: https://livewire.laravel.com/docs/3.x/properties
- Validaciones: https://livewire.laravel.com/docs/3.x/validation
- Paginación: https://livewire.laravel.com/docs/3.x/pagination
- Navegación: https://livewire.laravel.com/docs/3.x/navigate
- Pruebas: https://livewire.laravel.com/docs/3.x/testing
- Integración con Alpine.js: https://livewire.laravel.com/docs/3.x/alpine

## 3. Alpine.js

Framework JavaScript ligero utilizado para modales, menús, estados visuales, transiciones e interactividad del frontend.

- Documentación: https://alpinejs.dev/start-here
- Estado local (x-data): https://alpinejs.dev/directives/data
- Mostrar u ocultar (x-show): https://alpinejs.dev/directives/show
- Eventos (x-on): https://alpinejs.dev/directives/on
- Formularios (x-model): https://alpinejs.dev/directives/model
- Transiciones: https://alpinejs.dev/directives/transition

## 4. Redis

Base de datos en memoria utilizada como backend de caché, almacenamiento temporal y procesamiento de colas.

- Laravel + Redis: https://laravel.com/docs/12.x/redis
- Laravel Cache: https://laravel.com/docs/12.x/cache
- Laravel Queues: https://laravel.com/docs/12.x/queues
- Laravel Horizon: https://laravel.com/docs/12.x/horizon
- Documentación Redis: https://redis.io/docs/latest/
- Comandos Redis: https://redis.io/docs/latest/commands/

## 5. Predis

Cliente Redis para PHP que permite conectar Laravel con Redis sin depender de la extensión nativa PhpRedis.

- Repositorio: https://github.com/predis/predis
- Instalación e integración: https://laravel.com/docs/12.x/redis

## 6. Elasticsearch

Motor de búsqueda utilizado para indexar productos, buscar por relevancia, aplicar filtros y gestionar sinónimos.

- Documentación: https://www.elastic.co/docs/reference/elasticsearch
- Cliente oficial PHP: https://www.elastic.co/docs/reference/elasticsearch/clients/php/getting-started
- Query DSL: https://www.elastic.co/docs/explore-analyze/query-filter/languages/querydsl
- Mappings: https://www.elastic.co/docs/manage-data/data-store/mapping

## 7. Laravel Scout

Paquete Laravel que permite integrar modelos Eloquent con motores de búsqueda.

- Documentación: https://laravel.com/docs/12.x/scout

## 8. Elastic Scout Driver

Driver comunitario para utilizar Elasticsearch como motor de búsqueda de Laravel Scout. Requiere verificar compatibilidad de versiones.

- Repositorio: https://github.com/babenkoivan/elastic-scout-driver

## 9. Stripe

Plataforma de pagos utilizada para procesar compras y recibir confirmaciones mediante webhooks.

- Documentación: https://docs.stripe.com/
- Stripe Checkout: https://docs.stripe.com/checkout/quickstart
- Checkout Sessions API: https://docs.stripe.com/api/checkout/sessions/create
- Webhooks: https://docs.stripe.com/webhooks
- Pruebas de pagos: https://docs.stripe.com/testing
- Stripe CLI: https://docs.stripe.com/stripe-cli

## 10. Stripe PHP SDK

Librería oficial de Stripe para realizar operaciones con su API desde aplicaciones PHP y Laravel.

- Repositorio: https://github.com/stripe/stripe-php
- API Stripe: https://docs.stripe.com/api

## 11. Laravel Cashier (Stripe)

Paquete oficial de Laravel para integrar Stripe Checkout, facturación y suscripciones.

- Documentación: https://laravel.com/docs/12.x/billing
- Configuración: https://laravel.com/docs/12.x/billing#installation
- Modelos personalizados: https://laravel.com/docs/12.x/billing#using-custom-models
- Stripe Checkout: https://laravel.com/docs/12.x/billing#checkout

## 12. Spatie Laravel Permission v8

Paquete utilizado para administrar roles y permisos de usuarios, como admin y customer.

- Documentación: https://spatie.be/docs/laravel-permission/v8
- Instalación: https://spatie.be/docs/laravel-permission/v8/installation-laravel
- Uso básico: https://spatie.be/docs/laravel-permission/v8/basic-usage/basic-usage
- Permisos de roles: https://spatie.be/docs/laravel-permission/v8/basic-usage/role-permissions
- Roles frente a permisos: https://spatie.be/docs/laravel-permission/v8/best-practices/roles-vs-permissions

## 13. Laravel Authentication

Sistema de autenticación de Laravel para login, registro, sesiones, recuperación de contraseñas y protección de rutas.

- Autenticación: https://laravel.com/docs/12.x/authentication
- Autorización: https://laravel.com/docs/12.x/authorization
- Recuperación de contraseñas: https://laravel.com/docs/12.x/passwords
- Verificación de correo: https://laravel.com/docs/12.x/verification
- Sesiones: https://laravel.com/docs/12.x/session
- Middleware: https://laravel.com/docs/12.x/middleware

## 14. Laravel Fortify

Backend de autenticación para Laravel que proporciona funciones de login, registro, recuperación de contraseñas y autenticación de dos factores.

- Documentación: https://laravel.com/docs/12.x/fortify
- Starter Kits: https://laravel.com/docs/12.x/starter-kits

## 15. Laravel Socialite

Paquete oficial para implementar autenticación social mediante Google, Facebook y otros proveedores OAuth.

- Documentación: https://laravel.com/docs/12.x/socialite

## 16. Tailwind CSS

Framework CSS basado en clases utilitarias para desarrollar interfaces responsive.

- Documentación: https://tailwindcss.com/docs
- Instalación con Vite: https://tailwindcss.com/docs/installation/using-vite

## 17. Vite

Herramienta de desarrollo y compilación de recursos CSS y JavaScript utilizada por Laravel.

- Documentación: https://vite.dev/guide/
- Integración Laravel: https://laravel.com/docs/12.x/vite

## 18. DataTables

Librería JavaScript para tablas interactivas con búsqueda, ordenamiento, filtros y paginación.

- Documentación: https://datatables.net/manual/
- Server-side Processing: https://datatables.net/manual/server-side
- Opciones de configuración: https://datatables.net/reference/option/

## 19. MySQL

Sistema de base de datos relacional utilizado para productos, usuarios, pedidos, precios, inventarios y transacciones.

- Documentación: https://dev.mysql.com/doc/
- Integración Laravel: https://laravel.com/docs/12.x/database

## 20. MariaDB

Motor de base de datos relacional compatible con gran parte del ecosistema MySQL.

- Documentación: https://mariadb.com/docs/
- Integración Laravel: https://laravel.com/docs/12.x/database

## 21. Laravel HTTP Client

Cliente HTTP de Laravel utilizado para consumir APIs REST externas, como servicios de tipo de cambio.

- Documentación: https://laravel.com/docs/12.x/http-client

## 22. API REST de Banxico

Servicio del Banco de México para consultar series de datos financieros, incluidos tipos de cambio.

- Documentación: https://www.banxico.org.mx/SieAPIRest/service/v1/

## 23. Laravel Filesystem

Sistema de almacenamiento de archivos para imágenes de productos y documentos.

- Documentación: https://laravel.com/docs/12.x/filesystem

## 24. Laravel Scheduler

Sistema para ejecutar tareas programadas, como actualizaciones de tipo de cambio o sincronizaciones.

- Documentación: https://laravel.com/docs/12.x/scheduling

## 25. Laravel Pint

Herramienta oficial de Laravel para aplicar formato consistente al código PHP.

- Documentación: https://laravel.com/docs/12.x/pint

## 26. Pest PHP

Framework de pruebas para PHP que puede utilizarse con Laravel.

- Documentación: https://pestphp.com/docs

## 27. PHPUnit

Framework de pruebas unitarias y funcionales utilizado en proyectos PHP.

- Documentación: https://docs.phpunit.de/

## 28. PHP 8.5

Lenguaje de programación utilizado por el backend de Laravel.

- Manual: https://www.php.net/manual/es/
- Referencia de PHP: https://www.php.net/manual/en/

---

## API REST de Banxico — Tipo de cambio USD/MXN

Servicio oficial del Banco de México para consultar indicadores económicos, incluido el tipo de cambio FIX del dólar estadounidense frente al peso mexicano.

- Proveedor: Banco de México (Banxico)
- API: Sistema de Información Económica (SIE)
- Documentación oficial: https://www.banxico.org.mx/SieAPIRest/swagger/index.html
- Portal para obtener token: https://www.banxico.org.mx/SieAPIRest/service/v1/token
- URL base: https://www.banxico.org.mx/SieAPIRest/service/v1
- Serie del tipo de cambio FIX USD/MXN: SF43718
- Autenticación: token de API mediante encabezado Bmx-Token
- Método HTTP: GET
- Formato de respuesta: JSON

### Endpoints

Último tipo de cambio FIX disponible:
https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno

Histórico completo:
https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos

Histórico por rango de fechas:
https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/2026-11-01/2026-11-17

### Configuración Laravel (.env)

BANXICO_API_URL=https://www.banxico.org.mx/SieAPIRest/service/v1
BANXICO_TOKEN=TU_TOKEN_PRIVADO
BANXICO_SERIES=SF43718
BANXICO_CACHE_TTL=1800  
  


## API REST de Banxico — Tipo de cambio USD/MXN

Servicio oficial del Banco de México para consultar indicadores económicos, incluido el tipo de cambio FIX del dólar estadounidense frente al peso mexicano.

- Proveedor: Banco de México (Banxico)

- API: Sistema de Información Económica (SIE)

- Documentación oficial: [https://www.banxico.org.mx/SieAPIRest/swagger/index.html](https://www.banxico.org.mx/SieAPIRest/swagger/index.html)

- Portal para obtener token: [https://www.banxico.org.mx/SieAPIRest/service/v1/token](https://www.banxico.org.mx/SieAPIRest/service/v1/token)

- URL base: [https://www.banxico.org.mx/SieAPIRest/service/v1](https://www.banxico.org.mx/SieAPIRest/service/v1)

- Serie del tipo de cambio FIX USD/MXN: SF43718

- Autenticación: token de API mediante encabezado Bmx-Token

- Método HTTP: GET

- Formato de respuesta: JSON

### Endpoints

Último tipo de cambio FIX disponible:

[https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno](https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno)

Histórico completo:

[https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos](https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos)

Histórico por rango de fechas:

[https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/2026-11-01/2026-11-17](https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/2026-11-01/2026-11-17)

### Configuración Laravel (.env)

BANXICO_API_URL=[https://www.banxico.org.mx/SieAPIRest/service/v1](https://www.banxico.org.mx/SieAPIRest/service/v1)

BANXICO_TOKEN=TU_TOKEN_PRIVADO

BANXICO_SERIES=SF43718

BANXICO_CACHE_TTL=1800

### Uso previsto en el e-commerce

- Consultar el tipo de cambio FIX USD/MXN.

- Guardar temporalmente el resultado en Redis.

- Conservar fecha y origen de la cotización.

- Actualizar conversiones de precios conforme a la política comercial.

- Evitar consultar Banxico en cada petición del usuario.

- Mantener el tipo de cambio histórico utilizado en pedidos confirmados.

Nota: El FIX es un tipo de cambio de referencia y no necesariamente coincide con el precio de compra o venta de dólares de un banco o proveedor.



### Uso previsto en el e-commerce

- Consultar el tipo de cambio FIX USD/MXN.
- Guardar temporalmente el resultado en Redis.
- Conservar fecha y origen de la cotización.
- Actualizar conversiones de precios conforme a la política comercial.
- Evitar consultar Banxico en cada petición del usuario.
- Mantener el tipo de cambio histórico utilizado en pedidos confirmados.

Nota: El FIX es un tipo de cambio de referencia y no necesariamente coincide con el precio de compra o venta de dólares de un banco o proveedor.

## Arquitectura principal del proyecto

- Backend: Laravel 12 + PHP 8.5
- Frontend: Livewire 3 + Blade + Alpine.js
- Estilos: Tailwind CSS
- Compilación: Vite
- Base de datos: MySQL / MariaDB
- ORM: Eloquent
- Autenticación: Laravel Authentication
- Roles y permisos: Spatie Laravel Permission
- Caché y colas: Redis
- Búsquedas avanzadas: Elasticsearch + Laravel Scout (opcional)
- Pagos: Stripe / Laravel Cashier
- Servicios externos: Laravel HTTP Client
- Pruebas: PHPUnit / Pest
