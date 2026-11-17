# SDD-FE-BOOT-001 — E-commerce Laravel 12: bootstrap ejecutable, autenticación y frontend completo

**Versión:** 1.0 · **Estado:** plan de ejecución autorizado con límites estrictos · **Idioma UI:** español (México) · **Moneda:** MXN/USD  
**Perfil del ejecutor:** agente de IA **exclusivamente para operaciones de inicialización y desarrollo visual frontend**.  
**Prioridad:** este documento y `AGENTS.md` rigen la ejecución. Ante contradicción con la restricción académica/laboral de no usar IA en backend, prevalece la prohibición de generar backend.

## 0. Mandato principal (one-shot)

Preparar y **ejecutar localmente** una aplicación Laravel 12 para un e-commerce de hasta 1,000,000 productos, adelantando toda la experiencia visual de tienda, autenticación, cliente y administración con **Inertia.js + Vue 3 + TypeScript** y **Livewire** en el mismo monolito. Crear repo Git local, instalar dependencias permitidas, configurar entorno local seguro, ejecutar las **migraciones existentes y permitidas**, iniciar el servidor Laravel y Vite, abrir/verificar las rutas disponibles, y entregar un reporte con evidencia. El agente **NO puede escribir ni modificar lógica de backend**, aunque ello impida completar una ruta o una funcionalidad.

**El éxito del agente es entregar una app que arranque, pantallas coherentes e integrables y una lista objetiva de conexiones pendientes; no fingir un e-commerce operativamente terminado.**

## 1. Restricciones innegociables de autoría

### 1.1 Permitido
- Ejecutar herramientas CLI de instalación y arranque (`php`, `composer`, `npm`, `artisan`, `git`) **para bootstrap**, instalación de paquetes de procedencia oficial y verificación. Esto puede generar scaffolding estándar del proveedor, pero **NO autoriza que el agente programe, adapte, corrija ni genere lógica PHP propia**.
- Elegir **starter kit oficial Laravel 12 Vue/Inertia con autenticación integrada**, preferiblemente la variante nativa Laravel en lugar de WorkOS, para no introducir proveedor adicional. Verificar versiones instaladas: no asumir que todos los kits de Laravel 12 tienen el mismo Inertia.
- Instalar paquete Livewire oficial y dependencias frontend, siempre que la instalación no requiera modificar lógica de backend a mano.
- Crear y editar `resources/js/**`, `resources/css/**`, `public/**` (solo imágenes/assets no secretos), textos UX, componentes Vue, layouts, estados visuales, mocks en TypeScript, HTML y CSS de vistas Blade **puramente presentacionales**; respetar cualquier API y binding existentes.
- Ejecutar migraciones **ya escritas y auditadas por el desarrollador humano**, en una base de desarrollo vacía/desechable. Ejecutar instalaciones estándar del starter kit y sus migraciones oficiales tras inspección.
- Preparar reportes, contratos de backend esperados, manual de ejecución, checklist y pruebas visuales de frontend.

### 1.2 Prohibido
- Generar, editar o completar `app/**/*.php`, `routes/**/*.php`, `database/**/*.php`, `config/**/*.php`, `bootstrap/**/*.php`, clases Livewire PHP, controladores, servicios, middleware, policies, validadores, webhooks, jobs, seeds, factories, tests PHP, SQL de negocio o lógica backend en cualquier lenguaje.
- Modificar lógica del backend a través de scripts, comandos, scaffolds personalizados, automatismos de migración o terceros. **La única excepción es la generación convencional inalterada del framework/starter kit y los paquetes oficiales al instalarlos**. Registrar cuáles fueron generados por proveedores.
- Usar como backend entregable las migraciones de referencia producidas anteriormente mediante IA: **no cumplen la restricción de autoría**. Si el desarrollador las reimplementa o las aprueba según sus reglas, solo entonces pueden ejecutarse en un entorno desechable.
- Inventar endpoints, roles funcionales, respuestas del servidor, permisos, pagos confirmados, tasa Banxico real o stock confiable. Los mocks son siempre solo demostración visual y no se conectan a producción.
- Insertar credenciales de Stripe/Google/Banxico, usar datos personales reales, efectuar cargos, migrar una base compartida o producción, ejecutar `migrate:fresh`/`db:wipe`/`db:seed` sobre bases no desechables, hacer `git push` o publicar un repositorio sin permiso explícito.
- Cambiar versiones principales o instalar paquetes desconocidos para arreglar errores sin dejar justificación.

### 1.3 Regla si algo requiere backend
No intervenirlo. Continuar adelantando UI con datos mock e interfaces tipadas, documentar exactamente el archivo/endpoint/contrato faltante en `docs/BACKEND_HANDOFF.md`, con aceptación pendiente para el desarrollador humano. Prohibido etiquetar una simulación como funcionalidad real.

## 2. Decisión de autenticación — importante

**Usar autenticación de sesión/cookies del starter kit oficial de Laravel**, guard `web`, protección CSRF y rutas login/register/reset/profile proporcionadas por el kit. **No instalar Sanctum solo por usar Inertia o Livewire en un mismo monolito y dominio**; Inertia no necesita tokens para páginas Laravel web, y Livewire también utiliza sesión web. Sanctum es opcional y solo se agrega cuando un humano determine que habrá API para SPA desacoplada, móviles o terceros. Si el starter kit incorpora componentes auth PHP estándar, el agente puede dejarlos intactos, pero no modificarlos.

**Google OAuth:** requisito obligatorio del producto final, **no viene garantizado por la autenticación nativa del starter kit**. Preparar pantalla/botón «Continuar con Google» en estado deshabilitado o «pendiente de integrar» hasta que el humano implemente OAuth (por ejemplo Socialite, con callback validado y vínculo a usuario), y documentar el handoff. No crear una falsa sesión Google. No asumir que instalar Sanctum habilita OAuth.

**Roles:** `customer` y `admin` exigidos. El agente puede diseñar ambas áreas, pero ni crear middleware ni asignar roles. No exponer un enlace admin funcional a usuarios sin autorización verificada. Cualquier pantalla de admin visual aislada para demostración debe marcarse DEMO, no simular protección real. La navegación real a `/admin` solo se habilita cuando backend humano provea autorización.

## 3. Stack y límites técnicos

- **Laravel:** versión 12.x, PHP compatible con esa versión y Composer.
- **Frontend tienda / mi cuenta:** Inertia.js + Vue 3 + TypeScript + Tailwind CSS + Vite.
- **Frontend administración:** Livewire + Blade para vistas, con trabajo del agente limitado a presentaciones Blade/CSS, o componentes Vue de demo visual separada cuando no existan clases Livewire humanas. Livewire y el kit Inertia no son un único starter kit; integrar ambas tecnologías exige rutas/clases PHP del responsable humano cuando la instalación no provea pantallas funcionando.
- **Tablas:** DataTables es requisito para el dashboard real, preferiblemente modo **server-side** con paginación y filtros remotos; el agente puede crear maquetas visuales o integración JS contra contratos existentes, pero no crear endpoints DataTables PHP.
- **Base:** MariaDB/MySQL y migraciones humanas. Para ejecución local puede usarse SQLite **solo** para verificar el arranque del starter kit, nunca como evidencia de escala 1M ni sustituto del requisito MariaDB.
- **Redis:** caché, colas y eventual Horizon. Comprobar conectividad si existe, no programar servicios backend.
- **Elasticsearch:** índice de búsqueda e infraestructura de desarrollo si están disponibles; no programar indexación/sincronización ni declarar búsqueda real sin backend.
- **Stripe:** UI de carrito, resumen checkout y estado de pago demostrativos; cargos, PaymentIntents/Checkout Sessions y webhook por implementar manualmente en backend.
- **Banxico:** UI de selector MXN/USD, tipo de cambio y historial ilustrativo; no simular tasa vigente ni consultar Banxico desde frontend exponiendo secretos.
- **Datos:** 300,000 productos objetivo inicial y prueba de 1,000,000; **no generar 1M desde el agente ni ejecutar seeders de autoría IA**.

## 4. Inventario de funcionalidades visuales obligatorias

### 4.1 Tienda pública (Inertia/Vue)
- Home profesional con cabecera, buscador, categorías, destacados, novedades, ofertas, beneficios, footer.
- Catálogo con grid/lista, filtros laterales (categoría, marca, moneda, precio, disponibilidad), chips activos, ordenamiento, paginación remota conceptual, skeletons y estados vacíos.
- Detalle de producto: imágenes, variantes si aplica, precio MXN/USD claramente etiquetado, disponibilidad, ficha técnica, productos relacionados; CTA carrito solo funcional si endpoint humano existe, si no demo señalizada.
- Búsqueda visual con autocompletado, historial local no sensible, errores ortográficos simulados solo en mock; futura conexión Elasticsearch.
- Carrito visual (drawer/página), ajuste cantidades, eliminar, resumen, envío estimado; no calcular total definitivo con confianza de cliente.
- Checkout visual: dirección, envío, resumen, cupón, confirmación antes de pago; integración real Stripe bloqueada hasta backend humano.
- Páginas informativas: contacto, FAQ, políticas, términos, privacidad, devoluciones, 404/403.

### 4.2 Autenticación y cuenta
- Reutilizar las pantallas reales de login, registro, restablecer contraseña y verificación de email que ofrece el starter; rediseñar solo capa Vue/TS/CSS sin alterar acciones PHP.
- Botón Google visible **solo como pendiente/deshabilitado** hasta OAuth backend real; explicar por qué.
- `/account` o ruta existente equivalente: overview, perfil, datos de contacto, direcciones, pedidos, detalle/seguimiento, wishlist visual opcional, preferencia de moneda, seguridad de cuenta; cada acción debe distinguir conexión real, demo o pendiente.
- Estados: sin sesión, cargando, sesión caducada, error 401/403/419, y confirmaciones solo si backend confirma.

### 4.3 Administración (Livewire UI)
- Dashboard visual con tarjetas KPI de ventas, pedidos, productos, stock bajo, conversión MXN/USD, estado de procesos Redis/Elasticsearch, gráficos mock **rotulados demo**.
- Layout con sidebar, breadcrumb, menú usuario, notificaciones, light/dark, móvil accesible.
- Vistas de productos (DataTables server-side como contrato, no carga 1M en cliente), categorías, marcas, proveedores, inventario, lotes, costos históricos, precios y reglas, Banxico, usuarios/roles, pedidos, pagos Stripe, logs/sincronizaciones.
- Estados de tablas: carga, vacío, filtro, paginación, error, exportación visual pendiente de backend.
- Si no existen rutas Livewire/Blade seguras de administración, entregar plantillas Blade visuales y una galería de UI en el frontend de demo. **No inventar clases Livewire para habilitar rutas**.

## 5. Contratos de datos frontend (mock hasta entrega del backend)

Definir tipos TypeScript para `Product`, `ProductPrice`, `Category`, `Brand`, `Cart`, `Order`, `UserProfile`, `Address`, `ExchangeRateView`, `Paginated<T>`, `ValidationError`.

Campos mínimos `Product`: `id`, `sku`, `slug`, `name`, `description`, `category`, `brand`, `originCountry`, `images`, `availability`, `displayPrice` (importe, moneda, impuesto/estimación), `attributes`.

Reglas de presentación:
- Nunca inferir que `originCountry` define moneda; distinguir `purchaseCurrency`, `baseCurrency`, `basePrice` y `pricingPolicy` cuando se muestren en admin.
- Mostrar «Precio estimado» en mocks; no declarar cotización Banxico en vivo.
- El precio final y el inventario se validan exclusivamente en servidor en el producto real.
- Preparar estados de validación de formularios y errores, sin simular que el backend los aplica.
- La paginación no puede cargar el catálogo completo en el navegador. Datos mock limitados (p.ej. 24 productos) para la UI.
- Reactivos a cambio de moneda visual, pero conversión comercial definitiva debe venir del backend.

## 6. Plan de ejecución por gates — ejecutar autónomamente en orden

### G0 — Inspección segura
1. Inspeccionar `pwd`, SO, `php -v`, `composer --version`, `node -v`, `npm -v`, `git --version`, herramientas DB y servicios locales. No instalar nada global sin razón ni consentimiento previo.
2. Si ya existe repo, **no sobrescribir**: inspeccionar `git status`, `composer.json`, `package.json`, `.env.example`, migraciones, starter y rutas. Trabajar sobre lo existente y preservar cambios.
3. Si no existe, crear un directorio nuevo y repositorio Git local; usar nombre por defecto `ecommerce-laravel` solo si aún no hay nombre oficial. No crear remoto automáticamente.
4. Registrar versiones, comandos y bloqueos en `docs/EXECUTION_REPORT.md`.

### G1 — Proyecto y dependencias
1. Crear Laravel **12** utilizando el instalador oficial y seleccionar starter kit **Vue/Inertia con autenticación Laravel nativa** (no variante externa salvo requerimiento expreso). Si el instalador no permite asegurar la versión, detener la creación y documentar el bloqueo; no asumir Laravel 13.
2. Ejecutar instalación de dependencias existentes (`composer install`, `npm install`); instalar Livewire oficial, si el entorno y la restricción lo permiten, mediante comando de proveedor sin generar código PHP personalizado.
3. Generar `.env` local desde `.env.example` sin copiar secretos; configurar `APP_URL`, driver de sesión y conexión DB de desarrollo. `php artisan key:generate` en entorno local nuevo.
4. Instalar paquetes UI necesarios siempre que sean sostenibles y compatibles. Evitar una segunda arquitectura SPA independiente.

### G2 — Base de desarrollo y migraciones
1. Confirmar nombre/host de BD y que es **desechable**; preferir MariaDB. **No crear ni borrar bases ajenas**.
2. Revisar qué migraciones son del kit oficial y cuáles fueron escritas por el humano. **No ejecutar migraciones de referencia IA anteriores como backend entregable**.
3. Usar `php artisan migrate:status` y `php artisan migrate --force` **solo en BD local desechable confirmada**; registrar resultados y tablas. Nunca `migrate:fresh` automáticamente.
4. Si migraciones faltantes o fallan por razones de backend, **no corregir PHP/SQL**; entregar diagnóstico y continuar con frontend de demo.
5. No ejecutar seeder 1M sin confirmación explícita de base de prueba, capacidad y migraciones de autoría válida. El objetivo aquí es **app en ejecución y visual completo**, no pruebas de carga.

### G3 — Auth y ejecución real
1. Comprobar rutas de login/registro/profile y protección del starter existente. No modificar sus rutas/controladores.
2. Iniciar Laravel y Vite usando `composer run dev` si el proyecto provee ese script, o `php artisan serve` y `npm run dev` en procesos persistentes separados. Ejecutar `npm run build` y pruebas frontend disponibles.
3. Verificar con HTTP/navegador que `/`, `/login`, `/register` y páginas del starter respondan adecuadamente. Si se permite crear una cuenta de prueba, usar datos sintéticos y BD desechable, no credenciales reales.
4. Dejar procesos levantados si el entorno permite persistencia; registrar PIDs/puertos y forma de detenerlos. **No afirmar que siguen vivos si el entorno finaliza procesos al cerrar sesión del agente**.
5. Elegir `127.0.0.1` **o** `localhost` de forma consistente para evitar problemas de cookies/sesión. En un mismo monolito con Inertia no hace falta Sanctum.

### G4 — UI pública y cliente
1. Crear sistema de diseño: tokens, tipografía legible, spacing, componentes reutilizables, responsive, accesibilidad AA como objetivo y modo oscuro.
2. Implementar visualmente home, catálogo, búsqueda, detalle, carrito, checkout, cuenta, perfil, historial y estados de error usando Inertia/Vue/TS.
3. Mantener datos demo aislados en `resources/js/mocks/`; usar rutas existentes únicamente, y para vistas sin ruta entregar galería visual en una ruta ya disponible **solo si se logra sin modificar backend**; alternativamente documentar qué rutas debe crear el humano.
4. Probar navegación sin consola JS rota, vistas móvil/tablet/desktop, sin datos ficticios presentados como reales.

### G5 — Administración Livewire visual y DataTables
1. Verificar instalación de Livewire y estructuras Blade existentes.
2. Crear diseño admin y vistas Blade de presentación si puede hacerse sin modificar backend. No crear componentes de clase/acciones PHP.
3. Preparar módulos visuales DataTables con paginación remota como contrato; al no existir endpoint humano, usar un mock finito e indicar que falta integración real.
4. Se puede emplear DataTables JS para presentación cuando sea compatible, pero su integración server-side y autorizaciones quedan a cargo del humano.

### G6 — Auditoría y entrega
1. Ejecutar lint/typecheck/build frontend y pruebas visuales disponibles. Revisar responsive, teclado, focus, ARIA, contraste, carga/empty/error y claridad de divisas.
2. Confirmar `git diff --name-only`, que no se modificó ningún backend humano ni archivos protegidos; si un instalador creó archivos PHP oficiales, registrarlos aparte sin alterarlos.
3. Registrar rutas HTTP reales, qué funciona y qué es demo/pendiente; no marcar Google OAuth, Stripe, Banxico, Redis ni Elasticsearch como integrados por la sola presencia de pantallas.
4. Crear commit local de los artefactos permitidos y reportar hash, **solo si el repositorio no contiene cambios previos ajenos**. Nunca `git push` sin permiso.

## 7. Comandos de referencia (adaptar tras inspeccionar; no ejecutar a ciegas)

```bash
# Entorno y proyecto (la versión/kit se seleccionan y verifican al instalar)
php -v
composer --version
node -v
npm -v
git --version
laravel new ecommerce-laravel
cd ecommerce-laravel
composer install
npm install
php artisan key:generate

# Livewire, si no existe, por vía oficial
composer require livewire/livewire

# Base vacía autorizada: solo migraciones existentes de autoría permitida
php artisan migrate:status
php artisan migrate

# Comprobación y ejecución local
php artisan route:list
npm run build
composer run dev
# Si no existe el script: php artisan serve + npm run dev, cada uno en su terminal.

# Git local, no remoto
git status
git init  # solo si aún no es un repositorio
```

**Aviso:** `laravel new` es interactivo y puede resolver el último Laravel. El agente debe verificar que realmente creó Laravel 12, o usar un procedimiento oficial que fije 12 antes de continuar. Los comandos son ejemplos; el documento no autoriza afectar bases fuera del entorno.

## 8. Criterios de aceptación

- **A1:** Proyecto Laravel **12.x** creado o detectado, repo Git local disponible, dependencias instaladas sin cambios backend manuales por IA.
- **A2:** App Laravel y servidor Vite levantados y verificados por HTTP; se documentan puertos/procesos, errores y limitaciones de persistencia.
- **A3:** Login/registro/session auth nativos del kit probados donde sea factible; sin falsa integración Google.
- **A4:** UI Inertia/Vue de tienda, cuenta y checkout **con estados visuales coherentes**, responsive y accesible; mocks etiquetados.
- **A5:** UI administrativa Livewire/Blade adelantada hasta lo que permita la prohibición de PHP; tablas y contrato DataTables server-side documentados.
- **A6:** Migraciones existentes ejecutadas **solo si** son oficiales o de autoría humana y la base está verificada como desechable. Registro de resultado; las pendientes no se suplantan.
- **A7:** Sin falsa confirmación de pagos, autenticación, precio real, inventario ni cotizaciones.
- **A8:** Build frontend termina sin errores, cambios auditados y reporte de pendientes backend.

## 9. Entregables obligatorios

1. Repo local y árbol de archivos de frontend creado.
2. `docs/EXECUTION_REPORT.md`: comandos exactos, versiones, salida resumida, app URL comprobada, Vite, estado de DB/migrations, capturas o evidencia de UI, commits.
3. `docs/BACKEND_HANDOFF.md`: endpoints/properties/roles/autorizaciones/validaciones que debe implementar el humano; Google OAuth, Stripe, Banxico, Redis, Elasticsearch y DataTables.
4. `docs/FRONTEND_COMPONENTS.md`: catálogo de pantallas y componentes, estados mock/real/pendiente.
5. `.env.example` **sin secretos**, documentación de cómo arrancar y detener procesos, sin publicar `.env`.
6. Resumen final honesto con gates PASS/PARTIAL/BLOCKED, no solo una declaración de COMPLETE.

## 10. Dependencias funcionales que NO bloquean UI

- Motor de precio protegido por costos de lotes, Banxico y política comercial (`protected_dynamic`, etc.).
- Índices Redis/Elasticsearch, indexación asíncrona y recuperación de productos reales.
- CRUD de productos, proveedores, categorías, inventario; stock y checkout transaccional.
- Creación/confirmación idempotente de pedidos y Stripe webhook firmado.
- OAuth Google (vinculación segura y control de email verificado), roles customer/admin y políticas de acceso.
- DataTables server-side y filtros sobre millón de productos.

**No usar IA para implementar estos puntos backend; preparar contratos frontend y dejar el desarrollo al responsable humano.**

## 11. Instrucción final al agente

Ejecuta en una sola pasada los gates posibles sin pedir validaciones intermedias por tareas rutinarias. Si una operación peligrosa, un secreto o un desarrollo de backend exige intervención humana, **omite solo esa operación, documenta el bloqueo y continúa adelantando el frontend**. No digas que has iniciado Laravel o ejecutado migraciones sin evidencias. Prioriza una app local que abre realmente y un frontend completo, visible y preparado para conectar backend manual.
