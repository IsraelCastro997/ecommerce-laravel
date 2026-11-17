# E-commerce multidivisa — documentación base

**Estado:** SDD propuesto / arquitectura pendiente de aprobación y prueba. **Fecha:** 2026-11-14. **Idioma:** español.

## Descripción
E-commerce de catálogo grande, arquitectura monolítica Laravel, tienda pública Inertia/Vue, administración Livewire, MariaDB transaccional, Redis para caché/colas/sesiones, Elasticsearch para búsqueda, Stripe Checkout y tipo de cambio USD/MXN con API oficial Banxico. Precios dinámicos con protección de costos históricos y reposición. Sin Laya ni IA de búsqueda en alcance.

## Restricción fundamental
**Prohibido el uso de IA/agentes para escribir, editar, refactorizar, corregir o probar con código generado el backend.** La IA solo puede crear/modificar la presentación frontend aprobada. `AGENTS.md` define límites por ruta y protocolo de aprobación. Este paquete contiene **documentación de planificación, no implementación backend**.

## Lectura recomendada
1. `docs/requirements/PROJECT_BRIEF.md` — visión, alcance, decisiones y supuestos.
2. `docs/requirements/SDD.md` — especificación completa, requisitos con IDs y criterios.
3. `docs/architecture/ARCHITECTURE.md` — componentes, flujos, caché, búsqueda y costos.
4. `docs/architecture/DATA_MODEL.md` — entidades, invariantes, índices y ERD.
5. `docs/security/SECURITY.md` — controles y casos adversos.
6. `docs/integrations/INTEGRATIONS.md` — Banxico, Stripe, Elasticsearch, Redis.
7. `docs/testing/ACCEPTANCE.md` — matriz de pruebas y benchmarks.
8. `docs/planning/ROADMAP.md` — fases y gates.
9. `docs/operations/OPERATIONS.md` — operación, alertas y recuperación.
10. `AGENTS.md` — instrucciones vinculantes para agentes de frontend.

## Pendientes para confirmar con responsable de negocio
Tratamiento fiscal y jurisdicciones; si se cobra USD realmente o solo se muestra; tasas y rounding por mercado; política de promedio/FIFO; reservas de inventario y caducidad; envíos, devoluciones y facturación; alcance de variantes; equipo y límites de infraestructura. Estos supuestos **no autorizan implementación automática**.

## Referencias verificadas
- Laravel 12 Cache: https://laravel.com/docs/12.x/cache
- Laravel 12 Queues: https://laravel.com/docs/12.x/queues
- Laravel 12 Horizon: https://laravel.com/docs/12.x/horizon
- Elasticsearch pagination: https://www.elastic.co/docs/reference/elasticsearch/rest-apis/paginate-search-results
- Banxico SIE API: https://www.banxico.org.mx/SieAPIRest/swagger/index.html
- Stripe Checkout & webhooks: https://docs.stripe.com/checkout/fulfillment ; https://docs.stripe.com/webhooks/signature

**Nota:** Documentación técnica propuesta. No se ha implementado ni medido el sistema.
