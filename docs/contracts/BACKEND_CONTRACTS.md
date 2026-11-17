# Contratos funcionales pendientes

Estos contratos son propuestas para la conexión manual. La aplicación operativa usa las rutas de sesión y perfil que ya existían.

## Carrito y cotización

- Resolver carrito por sesión autenticada o identificador de invitado gestionado por el servidor. Definir expiración y conciliación al iniciar sesión.
- Las mutaciones aceptan el producto, la cantidad solicitada y la revisión del carrito. El servidor autoriza la cantidad, devuelve la versión y comunica conflictos de stock, precio y vencimiento por línea.
- Usar cadenas decimales con moneda explícita. Distinguir precio base, precio mostrado y precio confirmado. Devolver subtotal, descuentos, impuestos, envío y total mediante una cotización vigente e identificada. Para la evaluación, incluir la cotización MXN y USD, IVA 16 % y correo del comprador antes del prepedido, con reglas y redondeo definidos por el responsable.
- Asegurar idempotencia y validar revisión, cantidades y cotización antes de confirmar. El estado de pago confirmado solo llega por una respuesta autorizada del servidor.
- Ver propuestas de `Cart`, `CartItem`, `CartTotals`, `CartCurrency`, `CartValidationError`, `ShippingQuote` y `CheckoutSummary` en `resources/js/types/commerce.ts`.

## Panel de cliente

- Consultar únicamente direcciones y pedidos del usuario autenticado. Entregar historial paginado, detalle con líneas confirmadas y seguimiento si existe.
- Distinguir preferencia de moneda persistente en cuenta de la preferencia local de presentación. Compartir la sesión Laravel entre tienda, cuenta y futura administración.

## DataTables y paginación Laravel

| Entrada DataTables | Uso propuesto en servidor |
|---|---|
| `draw` | Devolver el mismo valor convertido a entero |
| `start`, `length` | Aceptar desplazamientos alineados al tamaño; convertir a página con `floor(start / length) + 1` y aplicar máximo acordado |
| `search[value]` | Búsqueda por campos permitidos dentro del ámbito autorizado |
| `columns[i]`, `order[i]` | Resolver índices a una lista blanca de columnas y direcciones; ordenar con desempate estable |
| Filtros visuales | Usar estado, fechas y filtros de negocio autorizados por módulo |

La respuesta devuelve `draw`, `data` con una página de Laravel, `recordsTotal` del conjunto autorizado antes de filtros y `recordsFiltered` con la cuenta del conjunto filtrado. Mostrar los estados de carga, error y sin registros; nunca responder con el catálogo completo. El modo estándar de DataTables usa offset y conteos. Para navegación secuencial asíncrona, evaluar cursor Laravel con UX compatible de anterior/siguiente. [Server-side de DataTables](https://datatables.net/manual/core/server-side) · [Paginación Laravel 12](https://laravel.com/docs/12.x/pagination)

## Integraciones

| Integración | Entregables del responsable |
|---|---|
| Stripe | Inicio idempotente, confirmación de servidor, webhook validado, conciliación, estados de error y reembolsos |
| Google OAuth | Login, alta pública con permisos mínimos, vinculación verificada y resolución de cuentas existentes |
| Banxico | Serie FIX, valor/fecha/origen, caché del último dato válido, reintentos, histórico y alerta de antigüedad |
| Redis | Caché de FX, colas, jobs, invalidación, TTL y contingencia; comportamiento definido en pérdida de servicio |
| Elasticsearch | Mapping, indexación incremental por outbox, filtros/agregaciones, sinónimos, reindexación y contingencia |
| Operación | Estado por servicio, última comprobación, fuente y métrica; usar estados disponible, degradado y fuera de línea solo con evidencia |

La configuración local reportó caché y colas de base de datos. `.env.example` define las variables `CACHE_STORE`, `QUEUE_CONNECTION`, `REDIS_HOST` y `REDIS_PORT`. Se encontraron puertos locales 3306 y 6379 en escucha y 9200 sin escucha. Un puerto abierto no valida credenciales, conectividad desde Laravel ni una integración activa.
