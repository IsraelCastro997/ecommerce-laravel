# Administración de demostración

Abrir `/dashboard` con una cuenta local y seleccionar **Administración demo**. La pestaña contiene datos ficticios. Las operaciones comerciales se mantienen deshabilitadas.

| Módulo | Vista |
|---|---|
| Resumen | Ventas, pedidos pendientes, productos activos y agotados, stock bajo, pagos, top tres de muestra e indicadores de integración pendientes |
| Productos | SKU, nombre, categoría, marca, precios USD y MXN, stock demo, disponibilidad y publicación. Formularios de alta y edición demo, atributos e imagen |
| Categorías y marcas | Tipos de catálogo, referencias y atributos |
| Inventario y lotes | Existencias, lotes, movimientos, reservas y costos ilustrativos |
| Proveedores | Producto, moneda de adquisición, costo cotizado y última compra demo |
| Precios | Precio base, política, costos, margen, piso, precios publicados MXN/USD y cambios demo |
| Banxico | FIX ilustrativo, histórico, fechas, sincronización y estado de antigüedad |
| Pedidos y Stripe | Pedidos, pagos, eventos ficticios, filtro de fechas y errores ilustrativos |
| Usuarios y permisos | Ejemplos de roles, permisos efectivos y auditoría |
| Sincronizaciones | Estados de ejemplo disponible, degradado y fuera de línea, con comprobación real pendiente |

`AdminTablePreview.tsx` ofrece encabezados semánticos, búsqueda local, filtro de estado y vistas de carga, vacío y error. El desplazamiento horizontal se limita al contenedor de la tabla. Los controles anterior/siguiente muestran la forma de paginar y permanecen deshabilitados porque los fixtures solo representan una página. DataTables será la integración manual solicitada.

Se añadieron dos componentes Blade de solo marcado en `resources/views/components/admin/`: estructura del panel y contenedor para tabla. El responsable puede reutilizarlos en Livewire 4 al crear la entrada protegida `/admin`. Actualmente no hay ruta administrativa, clase Livewire del proyecto ni navegación que lleve a una operación real.

[Contrato de tablas y permisos](../contracts/BACKEND_CONTRACTS.md) · [Páginas Livewire 4](https://livewire.laravel.com/docs/4.x/pages)
