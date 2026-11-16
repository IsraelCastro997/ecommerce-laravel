# Pantallas de Refugio Outdoor

Refugio Outdoor es una marca de demostración. El catálogo ofrece cuatro productos ilustrativos en una sola página, con precios independientes en MXN y USD. Los formularios comerciales son vistas de muestra.

| Entrada | Qué se puede ver o probar |
|---|---|
| `/` | Portada, categorías, catálogo, autocompletado local de nombres, filtros de categoría, marca, disponibilidad y precio, orden, cuadrícula/lista y estado vacío. |
| Detalle, carrito y checkout en `/` | Diálogo de producto con SKU, origen y relacionados. Carrito de demostración con cantidades, eliminación y checkout visual con dirección, envío y cupón. Los totales quedan pendientes de confirmación. |
| Ayuda en `/` | FAQ y vistas de aviso para contacto, privacidad, términos y devoluciones, pendientes de contenido oficial. |
| `/login`, `/register`, `/forgot-password` | Autenticación de sesión del starter y formularios de recuperación. Google se muestra como pendiente. |
| `/dashboard` | Cuenta autenticada con accesos al perfil y contraseña. Historial y direcciones de muestra. |
| `/dashboard` → Administración demo | Indicadores, gráfica con valores accesibles, estado operativo, módulos y estados de carga, vacío y error. |
| `/settings/profile`, `/settings/password`, `/settings/appearance` | Pantallas nativas de configuración, con textos de interfaz en español y tema claro, oscuro o sistema. |
| Vista Inertia `Error` | Presentación preparada para 401, 403, 404 y 419. Su conexión a las respuestas HTTP queda en el handoff. |

Los datos ilustrativos viven en `resources/js/mocks/store.ts`. La portada `public/store/camp.png` y el mosaico `public/store/products.png` se generaron con la herramienta integrada de imágenes.

- Portada. Fotografía panorámica del amanecer en un bosque de la Sierra Madre, con una tienda ocre a la derecha y sombra a la izquierda para el texto.
- Productos. Fotografía de estudio en cuatro cuadrantes de una mochila oliva, una tienda ocre, una botella verde de acero y una linterna frontal naranja y negra.

Evidencia disponible en [tienda de escritorio](evidence/tienda-desktop.png), [tienda a 320 px](evidence/tienda-320.png), [dashboard de escritorio](evidence/dashboard-desktop.png) y [dashboard a 320 px](evidence/dashboard-320.png).
