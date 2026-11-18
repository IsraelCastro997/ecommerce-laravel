# Dashboard de presentación

Vistas disponibles en las rutas administrativas existentes: `/admin`, `/admin/products`, `/admin/reports/sales` y `/admin/reports/top-products`. Se conservan autenticación y autorización; iniciar sesión no concede por sí solo acceso administrativo.

## Datos de ejemplo

`resources/js/demo/dashboard.json` contiene ocho productos, cinco pedidos y siete días de ventas. Son datos ficticios, sin IDs comerciales ni conexión a MariaDB. Las métricas, las gráficas y sus tablas accesibles leen ese JSON. El top de tres productos es una muestra semanal fija y está etiquetado como tal; no responde al filtro de fechas.

## DataTables y futura API

DataTables 3 está instalado desde npm. Las tablas usan `serverSide: true`, tamaño de página 10 y no permiten cambiar ese tamaño durante la muestra. El JSON representa una única página de menos de diez filas. El adaptador de demostración filtra y ordena esa muestra; no pagina ni descarga colecciones completas.

En `resources/js/dashboard-data.js`, `dashboardEndpoints.products` y `dashboardEndpoints.orders` permanecen en `null`. Para conectar el backend, asignar las URLs GET reales provistas por el desarrollador. No se han creado endpoints.

El transporte de `resources/js/dashboard-ui.js` envía los parámetros de DataTables (`draw`, `start`, `length`, `search[value]`, `order` y `columns`), además de `category` y `status`. Espera una respuesta JSON con esta forma:

```json
{"draw":1,"recordsTotal":300000,"recordsFiltered":1,"data":[{"sku":"EJEMPLO","name":"Producto de ejemplo","category":"Mochilas","price":1899,"stock":18,"status":"Activo"}]}
```

`data` debe contener exclusivamente la página solicitada. `recordsTotal` representa el total sin filtros y `recordsFiltered` el total con filtros, no solo el número de filas de esa página. El servidor deberá validar parámetros y permitir únicamente columnas conocidas para ordenar. Puede utilizar internamente la paginación nativa de Laravel; su respuesta debe adaptarse a este contrato de DataTables. No pasar una colección de cientos de miles de productos al navegador.

Para pedidos, cada fila incluye `number`, `date`, `customer`, `items`, `total` y `status`. Los importes de estas vistas están expresados en MXN. Las métricas usan un DTO independiente (`daily` y `top`); no se calculan desde la página de pedidos.

## Verificación

- Compilación Vite, ESLint y 12 pruebas frontend.
- Renderizado de Blade sin ejecutar operaciones comerciales.
- Navegador: filtros combinados, estado vacío, alternancia de gráfica y ausencia de errores JavaScript.
- Anchos 1440, 768, 390 y 320 px sin desbordamiento horizontal de la página; tablas desplazables con teclado.
- Gráficas con tabla alternativa, filtros etiquetados, foco visible y movimiento reducido.

No se modificaron controladores, rutas, permisos, modelos, migraciones ni servicios.
