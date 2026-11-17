# Pendientes de la evaluación y SDD-03

1. Confirmar con el evaluador la variante tecnológica. El enunciado solicita Livewire v3 con Alpine en tienda y administración. El responsable decidió conservar Livewire 4. Tienda y cuenta permanecen en React con Inertia.
2. Completar modelo de datos y relaciones en MariaDB. Validar equivalencias entre el esquema de referencia y los campos de la evaluación. Probar constraints, casts monetarios, stock, soft deletes y concurrencia antes del benchmark.
3. Integrar `HasRoles`, el guard `web`, seeder seguro y el acceso real `/admin` en Livewire. Verificar ownership y autorización por módulo y acción. Implementar altas, ediciones, activación y baja lógica con validación en servidor.
4. Conectar las tablas DataTables definidas en el SDD-02 a paginación Laravel. El enunciado original pide búsqueda y paginación en tiempo real con Livewire. Registrar cómo se cumplen ambas expectativas en los módulos acordados.
5. Implementar prepedido con correo, cotización MXN/USD, IVA 16 %, validación de stock y respuesta de cobro o validación bancaria. Conectar al menos un webservice REST permitido por la evaluación y caché de FX en Redis según esa rúbrica.
6. Conectar reportes de pedidos por fecha, ganancias por periodo y top tres de ventas. Distinguir mediciones reales de los ejemplos de interfaz.
7. Medir rendimiento en MariaDB desechable en fases de 300 mil y un millón de productos. Verificar consultas representativas, p95, consultas N+1, tamaños de página, índices y consistencia de la exportación. Consultar Elasticsearch únicamente tras validar su sincronización.
8. Preparar virtual host `tienda.test`, nombre de entrega del candidato, variables y credenciales de prueba bajo control del responsable. Publicar y compartir el repositorio cuando el responsable lo autorice.
