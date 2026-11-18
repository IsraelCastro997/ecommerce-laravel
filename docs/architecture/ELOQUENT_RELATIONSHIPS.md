# Relaciones Eloquent y pendientes

Revisión: 2026-11-17, basada en archivos actuales y estado de migraciones. No se modificaron modelos ni se ejecutaron operaciones comerciales.

- Product relaciona marca, categoría principal, categorías, atributos, proveedores, balances, lotes, movimientos, reservas, precios, snapshots, historial, carrito, pedidos, imágenes y detalles de compra. Product.images ordena por sort_order; product_images ya está migrada.
- ProductAttributeValue es un Pivot con timestamps deshabilitados y clave compuesta. Verificar sus operaciones con fixtures aislados antes de utilizarlo en escritura.
- PricingPolicy.products sigue suponiendo una FK pricing_policy_id que no existe en la definición de products. Definir el enlace con product_pricing y las versiones de política antes de usarla.
- User.purchaseOrders ya importa HasMany. User usa Billable de Cashier, pero no HasRoles de Spatie en el archivo inspeccionado. La configuración de identidad/permisos continúa a cargo del responsable.
- PurchaseOrder y PurchaseOrderItem ya tienen tablas migradas. La existencia de esas entidades no reactiva el módulo de compras a proveedores, excluido del alcance actual.
- StockReservation relaciona CartItem, OrderItem, Product y Warehouse. cart_item_id y order_item_id admiten null conforme a la migración de reservas de carrito. La regla de vinculación y transferencia es responsabilidad del backend; no se implementó en esta revisión.
- InventoryBalance pertenece a Product y Warehouse, con qty_on_hand y qty_reserved. Los contadores requieren reglas transaccionales del backend.
- PaymentTransaction pertenece a Order. StripeEvent no declara una FK de pedido/transacción: correlacionar la sesión Stripe con el pedido y procesar eventos es lógica que aún debe implementarse.
- OutboxEvent, SearchSynonym e IntegrationSyncLog dan soporte relacional a integraciones. Elasticsearch no requiere una FK hacia su índice ni un modelo Eloquent que sustituya Product.
- Redis no introduce una relación Eloquent con las descripciones: conserva copias temporales de products.description, que permanece en MariaDB.

## Verificación y límites

migrate:status informó 21 migraciones aplicadas. No se auditaron aquí todas las FK reales ni todas las lecturas/escrituras de relaciones. Validar soft deletes, pivotes, escalas y operaciones comerciales mediante una base MariaDB de pruebas aislada.

Ver [inventario vigente](ELOQUENT_MODELS.md) y [arquitectura de búsqueda/caché](SEARCH_AND_CACHE.md). La versión anterior se conserva en docs/history/architecture/2026-11-17-before-review.
