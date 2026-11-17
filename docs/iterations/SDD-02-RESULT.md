# Resultado de la iteración SDD-02

| Campo | Resultado |
|---|---|
| Incremento | SDD-02 |
| Estado | **PARTIAL** |
| Repositorio | `ecommerce-laravel` |
| Rama | `main` |
| Base de comparación | `22911a2`, migración a React. Primer arranque en `5d746e5` |
| Versiones verificadas | Laravel 12.72.0, PHP 8.5.12, Inertia Laravel 2.0.28, Inertia React 2.3.29, React 19.3.1, Livewire 4.4.8, Spatie 8.5.0, TypeScript 5.7.3, Tailwind 3.4.17, Vite 6.1.1 y binario MariaDB 10.11.16 |

Se revisaron el README del workspace, AGENTS.md, AGENTS_FRONTEND_BOOTSTRAP.md, el SDD inicial, los cuatro informes existentes en `docs`, el material extraído de referencia y el enunciado Word de la prueba. Los archivos `SDD.md`, `DATA_MODEL.md`, `ARCHITECTURE.md` y `ROADMAP.md` anunciados en el README padre no estaban presentes por esos nombres. La matriz usa lo publicado en las migraciones, las rutas y el Word.

## Inventario inicial y resultado

| Componente y archivo | Estado | Evidencia, dependencias y pendiente |
|---|---|---|
| Laravel, vendor y lock de Composer | EXISTING_WORKING | Validación de manifiesto, HTTP local y versión de framework comprobados |
| React, Vite e Inertia, `package*.json`, `resources/js/app.ts` | EXISTING_WORKING | Adaptador y rutas actuales se conservaron |
| Auth y perfil, controladores y `routes/auth.php`, `routes/settings.php` | EXISTING_WORKING | Los formularios dependen de la sesión Laravel y de las validaciones del starter |
| Eloquent, `app/Models/User.php` | EXISTING_INCOMPLETE | Solo `User`; relaciones, modelos comerciales y casts pendientes |
| Migraciones, `database/migrations` | EXISTING_WORKING | 10 aplicadas en SQLite local. El SDD inicial registra 10 en MariaDB desechable |
| Factories y seeders, `database/factories`, `database/seeders`, referencia externa | EXISTING_INCOMPLETE | Factory y seeder del starter; seed comercial masivo en referencia, fuera de ejecución |
| Middleware y providers, `bootstrap/app.php`, `app/Http/Middleware`, `app/Providers` | EXISTING_INCOMPLETE | Sesión e Inertia disponibles. Policies y autorización granular administrativa pendientes |
| Spatie, `config/permission.php` y migración publicada | EXISTING_INCOMPLETE | Paquete y esquema listos; `HasRoles`, asignación, permisos y controles pendientes |
| Carrito, `Welcome.tsx`, `useDemoCart.ts` y `DemoCart.tsx` | DEMO_ONLY | De memoria a persistencia local; stock, cotización y acciones comerciales pendientes |
| Cuenta, `Dashboard.tsx`, `Profile.tsx` | EXISTING_WORKING | Perfil nativo, moneda local y vistas de pedidos y direcciones demo |
| Administración, `AdminTablePreview.tsx`, `mocks/admin.ts` | DEMO_ONLY | Módulos con filas, filtros, formularios y estados de ejemplo. DataTables e inventario de servidor pendientes |
| Livewire, Composer y `resources/views/components/admin` | EXISTING_INCOMPLETE | Livewire 4 instalado, componentes Blade de solo marcado preparados |
| Entrada real `/admin` | MISSING | Ausente en `route:list`; la publicación requiere autorización de servidor |
| Stripe, FX, Redis, Elasticsearch y Google OAuth | MISSING | Revisión de configuración y estados visuales. Integraciones funcionales pendientes |
| Tests, `tests/Feature`, `tests/Unit`, `docs/testing` | BLOCKED para pruebas backend | Se conserva el suite del starter. Verificaciones frontend y script de navegador documentados |

## Correspondencia con la prueba técnica

La evaluación original solicita Laravel 12, Livewire v3, Alpine, MySQL, Redis y REST. El responsable decidió conservar **Livewire 4** y había confirmado **Inertia con React** para las pantallas de tienda y cuenta. Se conserva esa arquitectura en el proyecto. La conformidad literal de esa variante requiere validación con el evaluador.

| Requisito del Word | Evidencia actual |
|---|---|
| Laravel 12, catálogo, carrito y panel | Laravel confirmado. Catálogo, carrito y panel son demostraciones, con auth y perfil nativos |
| Livewire v3 y Alpine en tienda y CRUD | Divergencia tecnológica explícita. Livewire 4 instalado y tienda Inertia React |
| Tabla con SKU, descripciones, precios, imagen, inventario, vigencia y baja lógica; pedidos y detalle | Esquema normalizado de referencia con soft deletes y precios separados. Equivalencias y campos faltantes en auditoría |
| CRUD y permisos de administración | Formularios visuales. Ruta, writes, validaciones en tiempo real y autorización pendientes |
| Cotización MXN/USD, IVA 16 %, correo, stock y prepedido | Datos y controles de vista. Persistencia comercial, validación y cálculo en servidor pendientes |
| Peticiones a servicio REST y caché Redis de FX | Contratos documentados. Integración real y prueba pendientes |
| Reportes de pedidos por fecha, ingresos y top 3 | Filtro de fechas, gráfica de ventas y top tres de muestra. Datos de ganancias y ranking reales pendientes |
| Virtual host, repositorio remoto, credenciales de prueba | README local incorporado. Host de entrega, administrador y publicación pendientes |

## Gates

| Gate | Estado | Salida |
|---|---|---|
| A · Repositorio | PASS | Inventario, versiones y rutas |
| B · Esquema | PARTIAL | Tablas, claves, índices, tipos y huecos documentados. Estado SQLite comprobado |
| C · Modelos | BLOCKED | El AGENTS.md vigente reserva autoría de modelos y pruebas backend |
| D · Roles | PARTIAL | Spatie y esquema auditados, matriz propuesta y pruebas de permisos pendientes |
| E · Carrito | PASS en frontend demo | Persistencia local, controles, disponibilidad, moneda y checkout visual |
| F · Cliente | PASS en capa visual | Cuenta, perfil, preferencias y seguimiento de ejemplo |
| G · Admin | PARTIAL | React demo y Blade presentacional. Ruta autorizada, operaciones Livewire y DataTables pendientes |
| Rendimiento e integraciones | PARTIAL | Contratos y plan de aceptación. Escala e integraciones todavía sin verificar |

## Entregables

- [Esquema y modelos](../architecture/ELOQUENT_MODELS.md)
- [Auditoría de roles](../security/ROLES_PERMISSIONS_AUDIT.md)
- [Auditoría del carrito](../frontend/CART_AUDIT.md)
- [Componentes administrativos](../frontend/ADMIN_COMPONENTS.md)
- [Contratos y DataTables](../contracts/BACKEND_CONTRACTS.md)
- [Comandos y resultados](../testing/SDD-02-TESTS.md)
- [Pendientes SDD-03](../roadmap/SDD-03-PENDING.md)

El documento Word aportado por el responsable se usó como fuente de requisitos y se mantuvo fuera de los cambios de código. El historial de commits puede consultarse con `git log -3 --oneline`.
