# Inventario de Eloquent y esquema

La revisión se hizo sobre `database/migrations` y `app/Models`. El único modelo propio encontrado es `User`. Sus atributos asignables son `name`, `email` y `password`; el modelo oculta la contraseña y el token de sesión persistente. Los modelos `Role` y `Permission` pertenecen a Spatie. La definición de modelos, factories y pruebas de relaciones queda a cargo del desarrollador según el AGENTS.md vigente.

| Dominio | Tablas encontradas | Relaciones y particularidades para implementar |
|---|---|---|
| Identidad | `users`, `customer_profiles`, `customer_addresses`, `social_accounts` | Perfil 1:1 mediante `user_id` como PK; direcciones e identidades 1:N. Identidad externa única por proveedor e ID. |
| Roles | `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | Reutilizar la configuración y los modelos de Spatie. El modelo `User` todavía carece de `HasRoles`. |
| Catálogo | `products`, `brands`, `categories`, `product_categories` | SKU y slug únicos; marca y categoría principal opcionales; jerarquía de categorías; pivote compuesto producto-categoría sin timestamps. `products` tiene soft deletes e índices por estado, marca y categoría. |
| Atributos | `product_attributes`, `product_attribute_values` | Pivote compuesto producto-atributo con valor adicional y un índice para filtrado. Evitar un modelo con PK escalar ficticia para el pivote. |
| Proveedores | `suppliers`, `supplier_products` | Cotizaciones por pareja única proveedor-producto. El costo cotizado es `DECIMAL(18,4)`; contiene moneda de compra. |
| Inventario | `warehouses`, `inventory_balances`, `inventory_lots`, `inventory_movements` | Balance único por producto-almacén; lotes y movimientos con FK reales. Los movimientos pueden referenciar un lote y un usuario. El lote registra `receipt_reference` de texto. |
| Divisas | `currencies`, `exchange_rates`, `exchange_rate_sync_logs` | `currencies.code` es PK alfanumérica sin timestamps; tasas `DECIMAL(18,8)`; unicidad por fuente, serie y fecha. Los códigos de moneda son columnas de texto sin FK declarada. |
| Precios | `markets`, `product_pricing`, `pricing_policy_versions`, `price_publications`, `product_price_snapshots`, `price_history` | Precio base único por producto y mercado. La versión de política tiene configuración JSON y carece de FK directa desde `product_pricing`. Publicaciones enlazan mercado y tasa, snapshots enlazan publicación y producto. `price_history` utiliza nombre de tabla explícito y puede referenciar al actor. |
| Carrito | `carts`, `cart_items` | Usuario opcional, token de invitado único opcional, vencimiento y líneas únicas por carrito-producto. El esquema guarda cantidad y moneda; la cotización confirmada requiere contrato de servidor. |
| Pedidos | `orders`, `order_items`, `stock_reservations`, `stock_allocations` | Folio e idempotencia únicos. Pedido enlaza usuario, publicación y tasa opcionales. Detalle congela nombre, SKU y precio. Reservas enlazan ítem, producto y almacén. Asignaciones enlazan ítem y lote. |
| Pagos | `payment_transactions`, `stripe_events` | Las transacciones enlazan pedido. IDs externos Stripe únicos y opcionales según flujo. El evento tiene identificador externo único sin FK de pedido. |
| Búsqueda | `search_synonyms`, `outbox_events`, `integration_sync_logs` | Sinónimos por locale; outbox con agregado lógico e índice de pendientes; histórico de integración por servicio y fecha. Modelo relacional para documentos Elasticsearch queda fuera del esquema vigente. |

## Huecos y decisiones de precisión

- El esquema actual no tiene tablas para variantes, imágenes de producto, órdenes de compra, recepciones ni sus detalles. `search_synonyms` cubre términos generales, con alcance distinto de una relación con categorías. El índice de búsqueda externo puede consumir el outbox y evitar duplicar documentos como modelos Eloquent.
- Los campos de costos, piso manual, precio base y costo asignado tienen escala 4. El tipo de cambio tiene escala 8. Los importes publicados y de venta tienen escala 2. Preservar cada escala con cadenas decimales al serializar y documentar política de redondeo.
- Evitar asignación masiva de existencias, reservas, costos, roles, permisos, importes confirmados, estados de pago, referencias Stripe y claves de cotización. Cargar relaciones explícitas con columnas acotadas y una página a la vez.
- El enunciado pide campos adicionales de producto y un comprador identificado por correo en prepedido. En el esquema actual el stock está normalizado en balances, los precios se separan en políticas y snapshots, y el pedido no tiene correo explícito. Descripciones corta/larga, imagen y vigencia requieren propuesta de esquema o decisión de equivalencia documentada.
- Verificación histórica en MariaDB 10.11.16, 10 migraciones e índice `FULLTEXT`. En esta iteración se verificó el estado de 10 migraciones en SQLite, pendiente ejecutar pruebas de relaciones y validación comercial en MariaDB desechable.

[Relaciones Eloquent](https://laravel.com/docs/12.x/eloquent-relationships)
