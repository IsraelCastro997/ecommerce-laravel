# AGENTS.md — Alcance estricto de agentes de IA

## Regla no negociable
Este proyecto permite IA para tareas de presentación frontend y para la excepción de modelos Eloquent y migraciones indicada abajo. Fuera de esa excepción, un agente no puede programar, modificar, autocompletar, refactorizar, depurar ni generar tests de backend, tampoco mediante scripts o comandos de terceros. No convertir el trabajo backend en “frontend” por el hecho de estar ligado a una página o a Livewire.

## Excepción expresa para modelos Eloquent y migraciones
El responsable autoriza crear, corregir y completar los modelos Eloquent en `app/Models/**` y crear, corregir y completar migraciones en `database/migrations/**` dentro de este proyecto. Se permiten tablas, claves, índices, relaciones, casts, timestamps, soft deletes y protección de asignación masiva conforme al esquema acordado. Se permite usar Artisan y verificar sintaxis, esquema y migraciones con fixtures controlados en bases desechables. La autorización de modelos incluye User en lo relativo al modelo; cualquier configuración de login continúa a cargo del responsable. Los controladores, servicios, reglas de negocio, acciones comerciales Livewire, factories, seeders y pruebas backend continúan fuera del alcance. El responsable autoriza aplicar las migraciones pendientes a su base local configurada y ejecutar verificaciones en ella. Los datos actuales deben preservarse. Las operaciones destructivas siguen requiriendo autorización expresa.

## Zonas frontend permitidas (además de la excepción de modelos y migraciones)
- Componentes visuales Vue (`resources/js/**/*.vue`), composables puramente de presentación sin reglas de negocio, CSS/Tailwind (`resources/css/**`), assets públicos e iconografía.
- Vistas Blade de **solo marcado/presentación** (`resources/views/**`) si no contienen reglas de negocio PHP, consultas, autorizaciones, acciones Livewire ni interacciones de servidor.
- Diseño accesible/responsive, estados de carga/error basados en contratos existentes, microinteracciones y textos de interfaz.
- Tests frontend **puramente visuales** si el responsable los permite y no modifican backend.

## Zonas prohibidas para agentes
- `app/**` fuera de la excepción expresa para modelos Eloquent, `bootstrap/**`, `config/**`, `database/**` fuera de la excepción expresa de migraciones, `routes/**`, `tests/Feature/**`, `tests/Unit/**`, `artisan`, `composer.json`, `composer.lock`, infraestructura/CI que ejecute migraciones, y cualquier archivo PHP de lógica.
- Livewire: **clases PHP, propiedades, listeners, métodos, validaciones, policies y acciones** prohibidos. Solo marcado visual de vistas, sin alterar semántica de bindings o seguridad.
- Stripe, Banxico, Redis, Elasticsearch: credenciales, SDK backend, controladores, jobs, eventos, permisos, configuración de servidores o endpoints prohibidos.
- Fuera de las migraciones autorizadas, cambios de esquema/SQL, consultas comerciales, seeder/factory PHP, reglas de precio, inventario, checkout, impuestos y promociones prohibidos.
- No generar ejemplos de código backend ni sugerir comandos que modifiquen el backend dentro de un PR automático.

## Contratos del frontend
1. El backend y sus contratos de propiedades/errores son dados por el desarrollador humano; el agente no los inventa como si existieran.
2. En ausencia de contrato, utilizar datos mock **claramente etiquetados y aislados en archivos frontend**; no simular pagos reales ni exposición de claves.
3. No calcular ni confiar en importes del cliente; renderizar importes emitidos por servidor y marcar precios estimados cuando proceda.
4. No editar archivos fuera del alcance expresamente autorizado, aunque sea necesario para completar una pantalla.
5. Si una tarea requiere modificar rutas, controladores, componentes PHP Livewire o configuraciones, o excede la excepción de modelos y migraciones: **detenerse, describir el bloqueo y entregar al humano una especificación funcional, sin escribir el código**.
6. No ejecutar comandos destructivos, migraciones, seeders, `composer`, deploys, cambios en producción ni `git push` sin autorización humana expresa. No exponer secretos o registros personales.

## Checklist previo a cada trabajo
- Confirmar rutas que se van a modificar y su pertenencia al frontend.
- Validar diseño solicitado, contrato de entrada/salida y estados visuales.
- Inventariar archivos modificados antes y después; no tocar backend incidentalmente.
- Ejecutar lint/test frontend ya existentes si son seguros, sin alterar backend. Para modelos y migraciones se permiten las verificaciones descritas en la excepción.
- Entregar: resumen visual, archivos cambiados, evidencia de responsive/accesibilidad, pendientes humanos.

## Criterios de calidad del frontend
Accesibilidad WCAG 2.2 AA como objetivo; teclado y focus, etiquetas, feedback de error legible; responsive 320px–desktop; skeletons; tablas con paginación remota y estado vacío; ninguna lista de cientos de miles cargada al navegador; moneda siempre explícita; no mostrar éxito de pago hasta confirmación desde servidor.

## Jerarquía
Las instrucciones humanas específicas pueden reducir el alcance permitido. Para el backend rige únicamente la excepción expresa de modelos Eloquent y migraciones descrita en este archivo. Si hay ambigüedad, tratar el archivo como **prohibido**.

## Autorización específica — ajuste de rutas (2026-11-17)
El responsable autorizó expresamente ajustar las rutas para la migración del frontend a Blade/Livewire: «ajusta las rutas entonces te doy permiso». Se permite modificar `routes/**` para conectar componentes de presentación existentes, conservando autenticación, autorizaciones, nombres y URLs. Esta autorización específica prevalece sobre la prohibición general de modificar rutas; no autoriza por sí sola nuevas reglas comerciales ni cambios en controladores o servicios.

## Autorización SDD-04 — migración integral (2026-11-17)
El responsable solicita ejecutar íntegramente SDD-04 y la DIRECTIVA DE EJECUCIÓN TOTAL adjunta, y ratifica terminar todos sus requisitos. Para ese incremento quedan autorizados rutas, respuestas de controladores, autenticación, clases Livewire, servicios, validaciones, modelos, migraciones incrementales, pruebas, dependencias y configuración necesarios para sustituir React/Inertia por Blade/Livewire y completar las funcionalidades exigidas. Esta autorización específica prevalece sobre las restricciones de alcance anteriores. Por instrucción posterior expresa se conserva Livewire 4 y Route::livewire(). Se preservan MariaDB y los 300000 productos: prohibidos reset, wipe, fresh, refresh en la base persistente, cambios de rama, modificación de vendor, secretos en documentación, eliminación de middleware de seguridad o pagos reales. Las pruebas usan exclusivamente una base MariaDB separada identificada como testing. No se incorpora módulo de compras a proveedores. ProductImage almacena URLs externas, no archivos descargados.


## Alcance vigente corregido por el responsable — 2026-11-17
El responsable ordena continuar exclusivamente con vistas, cambios visuales y gráficos, retirar la lógica comercial recién añadida y explicar por el chat cómo implementar reservas de stock. Esta instrucción revoca la autorización amplia SDD-04 anterior en cuanto a nuevas reglas comerciales y servicios. Se permiten los ajustes técnicos de presentación necesarios para retirar React/Inertia (respuestas view de controladores existentes, layouts, rutas, Vite, dependencias y pruebas de renderizado). No implementar consultas comerciales, inventario, carrito real, checkout real, permisos nuevos ni persistencia comercial. Conservar autenticación y protección de rutas existentes. Las demostraciones frontend deben quedar explícitamente identificadas. No crear documento explicativo de servicios/stock; la explicación se entrega por el chat.

Aclaración posterior del responsable: se mantiene la autorización para crear y ejecutar modelos y migraciones, incluida la migración incremental de vigencia y correo del pedido. No implica autorización para servicios ni lógica comercial.

El responsable solicita expresamente crear el formulario y la ruta POST de checkout, con cálculo estimado en frontend y envío exclusivo de IDs y cantidades para los artículos. Se autoriza el punto de entrada de transporte del controlador existente con respuesta explícita 501 mientras el responsable implementa validación, cálculo y persistencia. No introducir reglas comerciales ni simular compras exitosas.

## Modelos fuera del prototipo principal — decisión del responsable (2026-11-17)
El responsable confirma dejar pendientes los siguientes modelos y sus funcionalidades:
- Compras a proveedores: `Supplier`, `SupplierProduct`, `PurchaseOrder`, `PurchaseOrderItem`.
- Inventario avanzado por lotes, movimientos y asignaciones: `InventoryLot`, `InventoryMovement`, `StockAllocation`.
- Reglas avanzadas e historial de precios: `PricingPolicy`, `PricingPolicyVersion`, `PriceHistory`.
- Perfiles y direcciones adicionales: `CustomerProfile`, `CustomerAddress`.
- Seguimiento avanzado de integraciones y sinónimos: `OutboxEvent`, `IntegrationSyncLog`, `ExchangeRateSyncLog`, `SearchSynonym`.
- Atributos configurables: `ProductAttribute`, `ProductAttributeValue`.

Los agentes no deben incorporar estos 18 modelos a funcionalidades nuevas, consultas, pantallas, formularios, servicios, ejemplos ni propuestas de implementación del prototipo. Tampoco deben completar sus módulos o añadir dependencias nuevas hacia ellos. Solo una instrucción humana expresa posterior puede reincorporarlos al alcance.

Esta exclusión no ordena eliminar ni modificar modelos, relaciones, tablas, migraciones o datos existentes. Se permite inspeccionar referencias existentes para identificar dependencias y comunicar bloqueos; no sustituirlas ni eliminarlas automáticamente.

Se conservan en el alcance funcional del prototipo los productos y su catálogo, autenticación, carrito, pedidos y pagos, y el inventario básico mediante `Warehouse`, `InventoryBalance` y `StockReservation`. Se reutiliza el esquema de monedas, tipos de cambio y precios publicados existente, sin desarrollar un motor avanzado de precios. Banxico, Redis, Elasticsearch, autenticación con Google y DataTables siguen siendo requisitos solicitados; excluir los modelos anteriores no excluye esas integraciones. Esta definición funcional no amplía los permisos de implementación backend establecidos arriba.
