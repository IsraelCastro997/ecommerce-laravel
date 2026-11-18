# Búsqueda de productos y caché de descripciones

Decisión del responsable: 2026-11-17. Estado: arquitectura objetivo documentada; no implementada.

## Responsabilidades

| Sistema | Responsabilidad | Fuente definitiva |
|---|---|---|
| MariaDB | Productos, descripción original, precios, inventario, usuarios, pedidos y pagos | Sí |
| Elasticsearch | Índice de búsqueda de productos, relevancia, filtros, orden y agregaciones | No; proyección reconstruible |
| Redis | Caché de descripciones consultadas con frecuencia | No; copia temporal reconstruible |
| Laravel | Consulta, autorización, composición de resultados y validación comercial | Aplica las reglas del servidor |
| Livewire / Blade / Alpine | Presentación de la página de resultados y estimaciones visuales | No decide precio ni stock definitivos |

Redis no reemplaza products.description. Si se pierde su contenido, la descripción se recupera de MariaDB. Elasticsearch no reemplaza las tablas de productos ni la validación de compra.

## Estado inspeccionado

- Laravel 12 y Livewire 4; MariaDB como base de desarrollo.
- No están instalados elasticsearch/elasticsearch ni laravel/scout.
- No está instalado predis/predis y la extensión redis no aparece en el PHP CLI inspeccionado.
- La configuración local sigue indicando CACHE_STORE=database, QUEUE_CONNECTION=database y REDIS_CLIENT=phpredis.
- La definición de un store Redis en config/cache.php es configuración base de Laravel; no demuestra conectividad ni uso por la aplicación.
- Hay tablas y modelos outbox_events, search_synonyms e integration_sync_logs. No se encontró una implementación que sincronice el catálogo con Elasticsearch.
- El frontend sigue mostrando ejemplos o DTOs suministrados por el backend. No hace consultas directas a Elasticsearch o Redis.
- No se comprobó en esta revisión que los servidores externos estén operativos ni se instaló ningún cliente.

## Elasticsearch: alcance previsto

Indexar una proyección por producto usando su ID estable de MariaDB como ID del documento. Incluir SKU, nombre, marca, categorías, estado y fechas de vigencia. Incluir description como campo de texto si también debe participar en la búsqueda; eso es independiente de servir el texto completo desde Redis.

Los precios publicados pueden formar parte de la proyección para filtros y orden, con moneda y unidades explícitas. No utilizarlos para autorizar el cobro: la cotización definitiva se consulta y valida en MariaDB. La disponibilidad indexada también puede quedar atrasada y no sustituye el inventario transaccional.

Definir mappings explícitos: identificadores y códigos exactos, campos de texto para búsqueda, fechas para vigencia y números adecuados para filtros. Evaluar análisis en español y el tratamiento de SKU, acentos y sinónimos con ejemplos reales del catálogo. No asumir que instalar el cliente crea estos mappings.

La búsqueda devuelve una página acotada de IDs y metadatos; Laravel compone los DTOs conservando el orden de relevancia. Debe revalidar que no se expongan productos eliminados, inactivos o fuera de vigencia. Los índices tienen consistencia eventual: la política para resultados obsoletos debe quedar explícita.

Conservar la interfaz de paginación nativa de Laravel mediante un adaptador de resultados de búsqueda. No paginar de nuevo toda la consulta Eloquent después de recibir los IDs de Elasticsearch, porque se perderían orden, total y criterios de búsqueda. Para navegación profunda habrá que definir una estrategia compatible con search_after y PIT, o limitar la profundidad visible; no cargar el catálogo completo en memoria.

## Redis: alcance previsto para descripciones

Usar caché bajo demanda: intentar leer la descripción en Redis y, si falta, consultar MariaDB y guardar la copia con caducidad. Evitar cargar de antemano las 300000 descripciones sin medir memoria, frecuencia de lectura y tamaño de cada texto.

Propuesta inicial de clave: product:description:{id}:{locale}:v{version}. El idioma solo debe formar parte de la clave si existe en el contrato. La versión debe cambiar cuando cambia la descripción; tener una columna version no implica que el sistema ya la incremente.

Propuesta inicial de TTL: 15 minutos, configurable y ajustable con mediciones. Es una decisión de diseño, no una configuración aplicada. La actualización o eliminación de un producto debe invalidar su caché después de confirmar la operación en MariaDB; no depender únicamente del TTL.

Si Redis falla, la aplicación puede recuperar la descripción de MariaDB y registrar la incidencia con una política de tiempos de espera. La caída de la caché no debería hacer desaparecer el producto. El acceso público sigue sujeto al estado y vigencia del producto.

Para dedicar Redis solo a estas descripciones no es obligatorio cambiar la caché global a Redis: Laravel permite elegir explícitamente el store Redis y conservar los demás usos en database. Configurar un cliente válido, credenciales y conexión sigue siendo necesario.

## Sincronización y volumen

La carga inicial del índice debe recorrer MariaDB por lotes y utilizar operaciones bulk, sin llevar 300000 o un millón de modelos al navegador ni a una sola colección PHP. El tamaño de lote requiere mediciones; no se fija como rendimiento garantizado.

Los cambios posteriores necesitan sincronización incremental tras el commit, reintentos y registro de fallos. El outbox existente es una opción para coordinarlo, pero la tabla por sí sola no implementa ese flujo. Actualizaciones repetidas o fuera de orden no deben sobrescribir una versión más reciente del documento. Los borrados lógicos deben retirar u ocultar el producto en el índice.

Para reconstrucciones completas, definir índices versionados y un alias de lectura que permita sustituir el índice terminado sin exponer uno parcialmente cargado. La migración de un índice externo es distinta de una migración SQL de Laravel.

## Dependencias y límites

- Cliente oficial PHP: elasticsearch/elasticsearch. Elegir una versión compatible con el servidor Elasticsearch realmente instalado antes de fijar una versión mayor.
- Scout es opcional. Laravel Scout no incluye un motor Elasticsearch de primera parte; haría falta un motor propio o un adaptador de terceros mantenido. No asumir que configurar SCOUT_DRIVER=elasticsearch basta.
- Redis: elegir PhpRedis o predis/predis y configurar el cliente correspondiente. No hace falta instalar los dos.
- No exponer credenciales ni conexiones de estos sistemas en el navegador. Laravel es el punto de acceso de la aplicación.
- Quedan por implementar conectividad, mappings, búsqueda, adaptación de paginación, sincronización e invalidación de caché. No hay servicios nuevos creados por esta revisión.

## Criterios de verificación pendientes

Buscar por nombre, SKU, descripción si se habilita, acentos y sinónimos; comprobar filtros, orden, páginas y exclusión de productos no vendibles. Comprobar cache hit/miss, caducidad, actualización de descripción y recuperación ante caída de Redis. Comprobar reindexación, eliminación lógica, reintentos y comportamiento ante caída de Elasticsearch sin inventar resultados.

## Referencias oficiales

- [Cliente PHP de Elasticsearch](https://www.elastic.co/docs/reference/elasticsearch/clients/php/getting-started)
- [Paginación de Elasticsearch](https://www.elastic.co/docs/reference/elasticsearch/rest-apis/paginate-search-results)
- [Laravel Cache](https://laravel.com/docs/12.x/cache)
- [Laravel Scout y motores disponibles](https://laravel.com/docs/12.x/scout)
