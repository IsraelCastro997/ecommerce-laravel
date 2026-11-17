# Revisión de asignación masiva SDD-03

Los modelos originales de inventario, pagos, carrito y pedidos conservan la protección por defecto de Eloquent. Los campos sensibles se asignarán por servicios autorizados. La definición de fillable es una capa adicional a las policies y la validación.

Product permite campos descriptivos. CustomerAddress permite información de dirección y mantiene user_id protegido. CustomerProfile permite preferencia de moneda y datos de contacto. El responsable debe autorizar a quién pertenecen esas filas antes de modificarlas.

Los modelos añadidos de compras permiten totales, costos, autoría, estado y referencias. Su uso queda pospuesto. Antes de habilitarlos, restringir entrada de usuario, calcular importes en servidor y asignar created_by desde sesión.

ProductImage permite product_id, disk, path e is_primary. La futura carga debe validar permiso del producto, contenido, ruta de almacenamiento y unicidad lógica de la imagen principal. Evitar aceptar rutas y discos arbitrarios.

User conserva name, email y password como asignables. Falta integrar HasRoles y verificar permisos reales. La ruta administrativa incorpora can:access-admin y requiere una capacidad definida y comprobada.

## Reserva de stock

La petición de reserva requiere propiedad del carrito, cantidad válida, idempotencia y fecha de expiración decidida por servidor. El navegador puede solicitar una cantidad; stock, cantidades autorizadas, dueño de reserva y transiciones son responsabilidad del servidor. Limitar apartados por sesión y su duración, con protección contra abuso.

Estado de aceptación PARTIAL. Pruebas de asignación masiva y autorización sobre base desechable pendientes.
