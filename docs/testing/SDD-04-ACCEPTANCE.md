# SDD-04 — Verificación del frontend

Fecha: 2026-11-17.

El alcance fue reducido expresamente por el responsable a vistas, gráficos, rutas y formulario de transporte. La lógica comercial permanece a su cargo. Livewire 4 se conserva por instrucción posterior.

| Comprobación | Resultado |
|---|---|
| Laravel / autenticación y renderizado | 51 pruebas correctas, 257 assertions, 0 fallos, 0 deprecaciones |
| Cálculos frontend y payload | 4 pruebas correctas |
| ESLint | Correcto |
| Vite build | Correcto |
| Chromium | PASS; 38 visitas verificadas; 0 errores JavaScript |
| Responsive | 320, 390, 768 y 1440 px; sin desbordamiento horizontal global |
| POST /checkout | Payload sin precios ni totales; respuesta explícita 501; 0 pedidos creados |
| React / Inertia | Sin paquetes ni referencias ejecutables en aplicación |
| MariaDB persistente | 300000 productos conservados; 0 pedidos creados |
| Protección administrativa | Invitado redirigido y usuario sin autorización rechazado |

## Evidencia

- browser-sdd04.cjs y sdd04-browser-result.json.
- screenshots/sdd04/home-desktop.png, home-320.png, home-390.png, home-768.png.
- screenshots/sdd04/cart-desktop.png y dashboard-desktop.png.
- tests/Feature/Presentation/BladePagesTest.php.
- tests/Frontend/checkout-presentation.test.js.

El navegador utiliza una cuenta sintética exclusivamente en ecommerce_frontend_testing y la elimina al terminar. Para comprobar el transporte se asigna un ID sintético en memoria únicamente durante esa prueba; no se atribuye a los productos de ejemplo ni se persiste. El controlador no crea pedidos. Las pruebas de renderizado administrativo autorizan un fixture solo dentro de PHPUnit; no cambian la autorización de la aplicación.

## Límites de aceptación

La migración de presentación funciona. El SDD integral original tiene estado PARTIAL porque el responsable excluyó su backend: consultas comerciales, catálogo real, reservas, carrito en base de datos, checkout real, pagos, reportes reales, permisos, Redis y API de divisas. No se declaran probadas esas funciones. El backend debe proporcionar IDs reales a la vista para habilitar envíos de productos reales; los ejemplos visuales se mantienen con product_id nulo.

Vite muestra avisos de Browserslist antiguo y de assets públicos resueltos en ejecución; ambos PNG se comprobaron en navegador. La CLI de Composer muestra una deprecación de su propia dependencia con PHP 8.5; las pruebas de la aplicación no tienen deprecaciones. npm informó vulnerabilidades en el árbol de herramientas existente; no se ejecutaron actualizaciones forzadas fuera del alcance.

No se realizó commit ni push.
