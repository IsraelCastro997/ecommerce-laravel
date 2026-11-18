# Revisión de frontend y rediseño del catálogo

Fecha: 2026-11-17. Alcance: Blade, Alpine, CSS, fuentes, interacción visual y pruebas. No se modificaron controladores, servicios, rutas, modelos, migraciones ni datos comerciales durante esta revisión. Los cambios de backend que aparecen en git status pertenecen al incremento anterior y se preservaron.

## Resultado de la revisión

Existen 50 vistas bajo resources/views/livewire. Las rutas actuales de tienda, cuenta y administración tienen una vista asociada y se verificó su renderizado. Esto no significa que todas sean pantallas terminadas: varias rutas administrativas todavía utilizan el mismo formulario y tabla genéricos, y los módulos comerciales continúan alimentados por ejemplos.

No se encontró una skill frontend en el proyecto. Se instaló frontend-design desde https://github.com/anthropics/skills/tree/main/skills/frontend-design mediante el instalador oficial de skills de Codex. Ubicación: C:/Users/Israel/.codex/skills/frontend-design/SKILL.md. Queda disponible para los siguientes turnos y se leyeron sus instrucciones durante este trabajo.

## Pendientes reales de frontend, por prioridad

P1 significa necesario para considerar terminada la interfaz del módulo; P2 significa mejora de experiencia. No se atribuye al frontend la responsabilidad de implementar negocio o seguridad de servidor.

| Prioridad | Módulo / archivo | Qué existe | Qué falta completar en frontend |
|---|---|---|---|
| P1 | Productos: livewire/admin/products/product-create, product-edit y product-detail | Las tres rutas existen | Pantallas propias de alta, edición y detalle. Actualmente las tres vuelven a mostrar admin/workspace y su tabla genérica. |
| P1 | components/admin/workspace | Modal de ejemplo con SKU, nombre, precios, stock, URL y fechas | Selector de categoría y marca, descripción, origen, campos y mensajes por formulario; precarga correcta de edición. Ahora usa importes fijos de ejemplo incluso al editar otra fila. |
| P1 | Categorías y marcas | Tabla compartida de categorías/marcas | Formularios propios de nombre y slug; jerarquía/estado de categoría donde corresponda. No deberían pedir precio, stock ni vigencia de producto. |
| P1 | Inventario, almacenes y atributos | Listados y modal genérico | Formularios de los campos de cada entidad; selección de almacén/producto, tipo de atributo y estados visuales específicos. No introducir un módulo de compras a proveedores. |
| P1 | Pedidos y pagos | Tablas de ejemplo | Detalle con líneas, cantidades, desglose de importes, estado del pedido/pago y referencias autorizadas; evitar usar el editor de productos en estos módulos. |
| P1 | Reportes sales-report y top-products-report | Resumen, barras de ventas y top de ejemplo reutilizados | Vistas de reporte propias; gráfica de ganancias diferenciada de ventas, desglose del rango seleccionado y tabla Top 3 específica. Las cifras actuales son ficticias. |
| P1 | Cuenta: address-manager | Modal básico de dirección | Campos con name de contrato, etiqueta, segunda línea, país y predeterminada; modos alta/edición/eliminación y errores por campo. La persistencia es backend. |
| P1 | Cuenta: order-detail | Ficha de dos pedidos ficticios | Tabla de artículos, cantidades y desglose coherente; estados vacíos, carga y error vinculados al contrato. Se corrigió el estado para referencias de ejemplo desconocidas. |
| P1 | Checkout | Correo, resumen estimado y POST con CSRF | Errores 422 junto a cada campo y estados de disponibilidad/cambio de importe aportados por el servidor. No mostrar confirmación sin respuesta autorizada. |
| P1 | Listas remotas | Filtrado y paginación de ejemplos | Controles vinculados al paginador nativo Laravel cuando exista el contrato: página, total, carga, error y reintento. No se integró DataTables. |
| P2 | Galería de producto | Imagen principal, URL externa y alternativa | Miniaturas y navegación de varias imágenes cuando el backend suministre su colección. No inventar imágenes asociadas a IDs reales. |
| P2 | Autenticación/perfil | Formularios nativos con CSRF y errores | Mensajes de éxito y de verificación amigables a partir de los estados reales del servidor; revisar títulos por página y autofoco tras errores. |
| P2 | Cabecera/ayuda/legal | Navegación y preguntas breves | Textos definitivos de contacto, términos, privacidad y envíos aportados por el responsable; no prometer políticas comerciales ficticias. |
| P2 | Accesibilidad completa | Foco visible, etiquetas, escape de modales y responsive comprobados | Auditoría completa de contraste, lectores de pantalla y recorridos de formularios administrativos. Las pruebas actuales no certifican WCAG AA. |

## Dependencias de backend, distintas de las vistas pendientes

- El catálogo sigue siendo de demostración; falta que el backend provea productos/IDs reales y metadatos de paginación.
- Carrito persistente en servidor, control de existencias y reservas: implementación humana.
- POST /checkout continúa respondiendo 501; validación, recálculo definitivo, pedidos y cobro no se implementaron en este trabajo.
- Permisos y autorización administrativa real: implementación humana. No se cambió auth ni can:access-admin.
- Reportes reales, Redis, API de divisas y servicios externos no se simularon como operativos.

## Cambios visuales realizados

- Catálogo con cabecera de montaña, composición editorial de dos columnas, navegación por categorías y búsqueda visible.
- Tarjetas con mayor protagonismo de imagen, tipografía y espacios consistentes, precio principal/secundario, disponibilidad y botón de bolsa.
- Filtros plegables, filtros activos que se pueden quitar, estados vacíos con recuperación y vista lista/cuadrícula persistente en el navegador.
- Vista rápida en dialog nativo: detalle, agregar a bolsa, cierre con Escape y retorno del foco al botón de origen.
- Admisión de imágenes por URL externa HTTP/HTTPS, con alternativa al fallar. El sprite de ejemplo se presenta mediante SVG con proporción preservada; no se alteraron sus píxeles ni se descargaron fotos para Laravel.
- Detalle de producto con imagen, precios, controles de cantidad, ficha de datos y acordeones.
- Aviso visual al agregar a bolsa; conserva la notificación accesible.
- Fuentes Bricolage Grotesque y DM Sans servidas localmente con sus licencias OFL; antes estaban declaradas pero no cargadas.
- Corregida la navegación de cabecera en tamaño tablet y la pantalla vacía para pedidos de ejemplo desconocidos.

## Criterios visuales aplicados

Paleta existente conservada: bosque #193d2d, tinta #172c22, arena #faf8f2, papel #fffdf8, musgo #65765b y ocre #eabd72. Bricolage Grotesque para titulares y DM Sans para texto/controles. La fotografía de equipo es el elemento principal; se redujo el uso de etiquetas decorativas y se mantuvo alineación izquierda para facilitar la exploración.

Escritorio: cabecera de texto/fotografía; categorías; barra de búsqueda/orden; filtros laterales y rejilla de productos. Móvil: imagen y texto apilados; categorías desplazables; búsqueda completa; filtros plegables; dos tarjetas por fila. La vista rápida usa el comportamiento nativo de dialog.

## Evidencia y comprobaciones

- npm run lint: correcto.
- npm run test:frontend: 8 pruebas correctas (filtros, búsqueda sin acentos, orden, URLs de imagen, estimaciones y payload sin precios).
- npm run build: correcto. Los avisos de assets públicos corresponden a recursos servidos desde public; las fuentes e imágenes se comprobaron en navegador.
- php artisan test --filter=BladePagesTest --compact: 24 pruebas correctas, 193 assertions, en MariaDB de pruebas aislada.
- Chromium: 1440, 768, 390 y 320 px; filtros, búsqueda, estado vacío, orden, vista lista, vista rápida, bolsa, Escape y retorno de foco.
- Capturas: docs/testing/screenshots/catalog-review/.
- Resultado automatizado: docs/testing/catalog-review-result.json.

No se creó lógica comercial ni se alteraron el esquema o los datos comerciales de la base persistente durante esta revisión. La entrega es un rediseño frontend y un inventario verificable de pendientes, no una declaración de que todo el backend del SDD está terminado.
