# Verificaciones SDD-03

Se inspeccionaron los archivos de modelos existentes, migraciones nuevas, rutas, factories del starter y el seeder comercial. Se ejecutó php -l sobre los directorios app/Models, app/Livewire, app/Http, app/Services si existe, database/migrations, database/factories y database/seeders.

Las pruebas de escritura con fixtures, autorización y concurrencia siguen pendientes bajo el AGENTS.md vigente. No se ejecutó migrate:fresh, ni se alteró el catálogo principal. El resultado histórico SQLite de la primera creación de modelos no valida las adiciones posteriores.

## Pruebas necesarias en MariaDB desechable

- Relación y namespace HasMany de User, policies y Spatie.
- Persistencia de ProductImage, pertenencia e imagen principal.
- Política de precio y vínculo real con producto o versión.
- Escalas monetarias respecto de columnas SQL.
- Lectura y escritura del pivote ProductAttributeValue con dos claves.
- Soft deletes en Product y en compras si se retoma ese módulo.
- Reserva de última unidad desde dos conexiones simultáneas.
- Liberación, expiración, cambio de cantidad y traslado a pedido con reintentos.
- Proteger el catálogo sintético principal al preparar fixtures y conexiones.

Estado PARTIAL. El inventario está basado en código y la sintaxis no demuestra integridad de relaciones o concurrencia.
