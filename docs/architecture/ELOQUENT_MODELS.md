# Inventario de Eloquent y esquema actual

Revisión: 2026-11-17. Se inspeccionaron los archivos de modelos, migraciones y composer.lock; migrate:status informó las 21 migraciones registradas como aplicadas en la base local. Esto no sustituye una auditoría de integridad ni pruebas de todas las operaciones comerciales.

Existen 39 modelos locales en app/Models. El alta inicial de 34 modelos convivía con el User del starter; después se incorporaron PricingPolicy, ProductImage, PurchaseOrder y PurchaseOrderItem. Cashier y Spatie también proporcionan modelos propios desde sus paquetes.

| Dominio | Modelos locales / tablas | Estado y particularidades |
|---|---|---|
| Identidad | User, CustomerProfile, CustomerAddress, SocialAccount | User conserva la autenticación Laravel e incorpora Billable de Cashier. CustomerProfile utiliza user_id como PK. |
| Roles | Modelos Role y Permission del paquete Spatie | Las tablas de permisos están migradas. User no incluye HasRoles en el archivo inspeccionado; no declarar la autorización administrativa completada. |
| Catálogo | Product, Brand, Category, ProductAttribute, ProductAttributeValue | products contiene SKU/slug únicos, soft deletes, marca/categoría principal y fechas available_from/available_until. Atributos con pivote compuesto. |
| Imágenes | ProductImage / product_images | Tabla y modelo existentes, relación Product.images ordenada por sort_order. La intención es usar URLs externas; el esquema actual tiene disk/path y no una columna url dedicada. |
| Proveedores | Supplier, SupplierProduct | Cotizaciones por proveedor/producto, moneda y costos de escala 4. |
| Compras | PurchaseOrder, PurchaseOrderItem | Tablas y relaciones existentes. El módulo funcional de compras está fuera del alcance actual por decisión del responsable. |
| Inventario | Warehouse, InventoryBalance, InventoryLot, InventoryMovement | Balances por producto/almacén; qty_on_hand y qty_reserved. Lotes, costos y movimientos separados. |
| Divisas | Currency, ExchangeRate, ExchangeRateSyncLog | Currency.code es PK alfanumérica, sin timestamps. Tipos de cambio de escala 8. |
| Precios | Market, PricingPolicy, PricingPolicyVersion, ProductPricing, PricePublication, ProductPriceSnapshot, PriceHistory | Precios normalizados por mercado y publicación. PricingPolicy y PricingPolicyVersion son entidades distintas; no asumir una FK que no existe. |
| Carrito | Cart, CartItem | Líneas únicas por carrito/producto; usuario o token de invitado. No implica que el carrito del frontend ya persista mediante estos modelos. |
| Pedidos | Order, OrderItem, StockReservation, StockAllocation | orders incluye customer_email; líneas con snapshots. stock_reservations tiene cart_item_id y order_item_id nullable tras la migración de carrito. |
| Pagos | PaymentTransaction, StripeEvent | Referencias Stripe únicas y esquema para registrar eventos; la lógica de cobro y procesamiento de eventos sigue incompleta. |
| Búsqueda / integraciones | OutboxEvent, SearchSynonym, IntegrationSyncLog | Tablas auxiliares existentes. No equivalen a un índice Elasticsearch operativo. |
| Cashier | Columnas Stripe en users; subscriptions y subscription_items | Las cinco migraciones publicadas por Cashier aparecen aplicadas. Modelos de suscripción proporcionados por Cashier. |

## Correcciones frente al documento anterior

Ya existen product_images, purchase_orders, purchase_order_items y pricing_policies. products ya tiene vigencia y orders correo de cliente. StockReservation ya puede referenciar al carrito. MariaDB es el motor local; las verificaciones SQLite de documentos anteriores son evidencia histórica y no el objetivo de desarrollo actual.

Product.description permanece en MariaDB como descripción original. Redis será una caché temporal de ese campo y Elasticsearch un índice reconstruible para buscar productos. No crear modelos Eloquent adicionales solo para representar copias de un mismo producto en esos sistemas. Ver [búsqueda y caché](SEARCH_AND_CACHE.md).

## Precisión y pendientes

Costos/precio base utilizan escala 4, tipos de cambio escala 8 e importes de venta escala 2 según sus migraciones. Evitar convertir valores monetarios definitivos a float como fuente de verdad. Precios y cantidades del navegador deben revalidarse en servidor.

PricingPolicy.products actualmente presupone pricing_policy_id en products; no se encontró esa columna en las migraciones. La relación necesita decisión/corrección humana antes de utilizarla. La importación HasMany de User.purchaseOrders ya existe: no sigue siendo un error pendiente de importación.

La existencia de relaciones no implementa reservas, checkout, sincronización de búsquedas ni invalidación de caché. Esas reglas permanecen a cargo del desarrollador.

La evidencia inicial de 83 relaciones y 30 casts en SQLite se conserva en el [documento histórico](../history/architecture/2026-11-17-before-review/ELOQUENT_MODELS.md); no se presenta como verificación actual de escrituras en MariaDB.
