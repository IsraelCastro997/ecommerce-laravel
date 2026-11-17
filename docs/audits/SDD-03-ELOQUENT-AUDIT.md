# Auditoría SDD-03

Estado PARTIAL. Se hizo revisión estática de los modelos, migraciones nuevas, rutas y scaffolds de carrito/checkout. La autorización vigente permite crear modelos nuevos y lectura de esquema. La corrección de modelos existentes, User, cambios de esquema y pruebas backend requiere implementación del responsable.

## Alcance actualizado

Compras a proveedores y recepciones se posponen por instrucción del responsable. La prioridad comercial es reservar stock al agregar al carrito, con vencimiento y control transaccional. [Especificación funcional](../../../docs/STOCK_Y_RESERVAS_CARRITO.md).

## Hallazgos

| Archivo o área | Estado | Hallazgo |
|---|---|---|
| User.php | NEEDS_FIX | purchaseOrders declara HasMany sin importar la clase de relaciones. PHP resuelve el tipo como App\\Models\\HasMany. Corregir antes de invocar esa relación |
| User.php | MISSING_RELATION | Falta HasRoles y las relaciones inversas de perfil, direcciones, carritos, pedidos y cuentas sociales |
| PricingPolicy.php | SCHEMA_MISMATCH | products() supone products.pricing_policy_id; esa columna no aparece en las migraciones inspeccionadas |
| pricing_policies | NEEDS_FIX | code se declara decimal(60) aunque el nombre sugiere identificador. strategy incluye protected_dinamic, diferente de protected_dynamic en product_pricing y el seeder |
| PurchaseOrder.php | NEEDS_FIX | Importa SoftDeletes y la migración tiene deleted_at, pero el modelo no utiliza el trait |
| PurchaseOrder.php | INVALID_CAST | exchange_rate tiene cast decimal:8 y columna decimal(10,4); subtotal y total tienen cast decimal:4 y columnas decimal(10,2) |
| purchase_orders | NEEDS_FIX | created_by usa nullable después de constrained; revisar el SQL efectivo y ubicar la nulabilidad de la columna correctamente |
| ProductImage.php | SECURITY_RISK | product_id, disk, path e is_primary son asignables. Autorizar producto propietario, destino permitido y reglas de imagen principal |
| PurchaseOrder y PurchaseOrderItem | SECURITY_RISK | Campos financieros y de autoría admiten asignación masiva. Dominio de compras pospuesto |
| stock_reservations | SCHEMA_MISMATCH respecto al alcance nuevo | Requiere order_item_id. Falta representación de una reserva de carrito anterior al pedido |
| Controladores Cart y Checkout | Pendiente | Son scaffolds sin acciones comerciales |
| Ruta admin/products | Pendiente | Existe grupo auth y can:access-admin. La definición de esa capacidad y su cobertura deben verificarse antes de declararlo funcional |

Los 34 modelos creados originalmente tuvieron verificación histórica SQLite de 83 relaciones y 30 casts. Ese resultado corresponde al estado previo a los cambios del responsable. Los nuevos modelos y las nuevas migraciones requieren validación específica contra el esquema SQL aplicado.

## Correcciones

Esta revisión entrega especificaciones. No se aplicaron correcciones al código del responsable. Estado de Spatie y pruebas de persistencia pendientes. Ver [relaciones](../architecture/ELOQUENT_RELATIONSHIPS.md), [seguridad](../security/MASS_ASSIGNMENT_REVIEW.md) y [verificaciones](../testing/SDD-03-MODEL-TESTS.md).
