# Integración a cargo del desarrollador

La tienda abre en `/` y el panel de cuenta en `/dashboard`. La administración incluida es una pestaña de demostración con datos locales. La entrada administrativa real deberá exigir autorización de servidor antes de devolver datos, ejecutar acciones Livewire o habilitar exportaciones.

| Área | Contrato funcional pendiente |
|---|---|
| Catálogo y detalle | Entregar la página actual con enlaces de paginación del servidor. Cada producto necesita identificador, SKU, nombre, categoría, marca, descripción, imágenes, disponibilidad y precio de presentación con moneda explícita. Acordar filtros, orden permitido, resultado vacío y error. |
| Búsqueda y disponibilidad | Conectar la fuente elegida para búsqueda. El servidor conserva la autoridad de inventario y valida cantidades antes de confirmar un pedido. Redis y Elasticsearch quedan pendientes de integración. |
| Carrito y checkout | Entregar cantidad aprobada, subtotal, descuentos, envío, impuestos, total y moneda desde el servidor. Resolver errores por campo y cambios de inventario o precio. |
| Stripe | Crear el flujo de cobro y actualizar su estado desde la confirmación del servidor. El checkout demo tiene deshabilitada la acción de pago. |
| Cuenta | Mantener las rutas nativas de perfil, contraseña y apariencia. Conectar direcciones, pedidos, seguimiento y preferencia persistente de moneda con el usuario autenticado. Revisar el requisito de verificación de email y configurar el transporte de correo. |
| Google | Activar el botón tras implementar Google OAuth y la vinculación de identidades. El registro público debe conservar los permisos mínimos. |
| Administración | Crear la entrada protegida y los componentes Livewire. Aplicar permisos en lecturas, acciones, filtros y exportaciones. Conectar KPIs y áreas de catálogo, proveedores, stock, lotes, costos históricos, precios, roles, pedidos, pagos y sincronización. |
| Roles y permisos | Spatie 8.5.0 está instalado, con la configuración y migración publicadas por Artisan. El responsable integra el modelo, define permisos y asigna los roles iniciales `customer` y `admin` usando el guard web. Verificar por separado la protección de cada operación. [Integración de Spatie](https://spatie.be/docs/laravel-permission/v8/installation-laravel) |
| Banxico | Entregar tasa, serie, fecha efectiva, historial y estado de actualización. La tasa del panel es ilustrativa. |
| Errores | Conectar la vista Inertia `Error` cuando estén disponibles las respuestas de error del proyecto. Recibe el código HTTP real en la propiedad `status` y contempla 401, 403, 404 y 419. |

## Paginación y tablas

Laravel será la fuente de paginación. Para páginas numeradas y conteo total, usar el contrato del paginador `paginate()`. Para catálogos grandes con navegación anterior/siguiente, `cursorPaginate()` es una opción cuando el orden es estable y usa una clave única o combinación única indexada. Reiniciar la navegación cuando cambian los filtros o el orden. [Paginación de Laravel 12](https://laravel.com/docs/12.x/pagination)

| Opción | Encaje con el proyecto |
|---|---|
| jTable | Plugin de jQuery para CRUD por AJAX. La serie más reciente marcada por GitHub es 2.6.0. Conservar como integración manual si es requisito de la entrega. [Repositorio y versiones](https://github.com/volosoft/jtable/releases) |
| DataTables | La documentación del proyecto indica DataTables 3.1.5 con integración Vue 4.0.0 y protocolo server-side propio. Si se usa su paginación interna, la integración debe adaptar el resultado del paginador Laravel al protocolo de DataTables. [Versiones oficiales](https://api.datatables.net/versions/detail) · [Protocolo server-side](https://datatables.net/manual/core/server-side) |
| TanStack Table Vue | Opción moderna para Vue cuando se necesitan columnas interactivas. El modo manual recibe filas ya paginadas y permite conservar en la UI los enlaces que entrega Laravel. GitHub muestra aproximadamente 28.6 mil estrellas frente a 1.1 mil de jTable. Esa cifra refleja interés en el repositorio. [Paginación manual en Vue](https://tanstack.com/table/latest/docs/framework/vue/guide/pagination) · [TanStack en GitHub](https://github.com/TanStack/table) · [jTable en GitHub](https://github.com/volosoft/jtable) |

Las tablas interactivas quedan para la fase manual solicitada. Acordar primero si la evaluación exige específicamente jTable o permite DataTables o TanStack. Para un millón de filas, probar los filtros reales sobre índices y medir consultas antes de fijar el tamaño de página. El seeder de volumen se conserva en la referencia extraída, fuera de la aplicación.
