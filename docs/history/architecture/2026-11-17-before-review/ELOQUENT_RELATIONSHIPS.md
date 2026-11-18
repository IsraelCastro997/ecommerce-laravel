# Relaciones revisadas en SDD-03

Product enlaza marca, categoría principal, categorías, atributos mediante pivote, proveedores, balances, lotes, movimientos, reservas, precios, snapshots, historial, carrito y pedido. La relación images añadida apunta a ProductImage y ordena por sort_order. Su tabla está descrita en una migración nueva y requiere confirmar que se aplicó al motor destino.

ProductAttributeValue es un Pivot con timestamps deshabilitados. Las escrituras deben realizarse a través de la relación product_attribute_values con ambas claves y value. Comprobar attach, lectura, actualización y detach con fixtures aislados en MariaDB.

PricingPolicy.products supone una FK ausente en la definición products inspeccionada. Decidir el enlace entre política, versión de política y product_pricing antes de crear relaciones adicionales.

User.purchaseOrders necesita corregir la resolución del tipo HasMany. Las relaciones de identidad y Spatie siguen pendientes de completar manualmente.

PurchaseOrder, PurchaseOrderItem y relaciones desde Supplier y Product existen como archivos nuevos. Se pospone su desarrollo funcional por la decisión del responsable.

StockReservation pertenece al detalle de pedido, producto y almacén. InventoryBalance pertenece a producto y almacén, con cantidades on_hand y reserved. El vínculo de reserva a carrito y su transferencia posterior al pedido requieren decisión de esquema.

PaymentTransaction pertenece a Order. StripeEvent no tiene FK hacia transacción declarada en el esquema original. La correlación de eventos de pago requiere contrato explícito.

## Pendientes de validación real

Confirmar tablas y FK aplicadas, esquema de timestamps y escalas. Probar resultados de relaciones con datos mínimos, incluyendo recursos con soft deletes y pivotes compuestos. Verificar en MariaDB antes de declarar compatibilidad de escritura.
