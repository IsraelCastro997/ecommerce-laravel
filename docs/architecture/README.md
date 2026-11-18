# Arquitectura vigente

Revisión: 2026-11-17. Los documentos separan el estado comprobado del repositorio de la arquitectura que el responsable quiere implementar.

- [Frontend actual](FRONTEND-ARCHITECTURE.md): Laravel 12, Livewire 4, Blade y Alpine.
- [Esquema y modelos actuales](ELOQUENT_MODELS.md): inventario de 39 modelos locales y migraciones registradas.
- [Relaciones y pendientes](ELOQUENT_RELATIONSHIPS.md): relaciones existentes y limitaciones verificadas.
- [Elasticsearch y Redis](SEARCH_AND_CACHE.md): objetivo aprobado de búsqueda de productos y caché de descripciones. Integración aún no implementada.

Se corrigieron afirmaciones antiguas que indicaban que no existían imágenes, compras, vigencia, correo del pedido ni vínculo de reservas con carrito. Las versiones previas se preservaron en docs/history/architecture/2026-11-17-before-review.

Esta revisión modifica únicamente documentación. No instala paquetes, cambia configuración, ejecuta migraciones ni implementa servicios. El estado de migraciones se consultó mediante migrate:status, en modo de lectura. La existencia de una tabla o un paquete no acredita el funcionamiento de una integración.
