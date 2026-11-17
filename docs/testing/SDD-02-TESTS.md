# Verificación de SDD-02

| Comprobación | Resultado |
|---|---|
| `composer validate --no-check-publish` | Manifiesto válido. Composer 2.8.6 mostró un aviso de deprecación con PHP 8.5 |
| `php artisan about --only=environment,drivers` | Laravel 12.72.0 local, SQLite, sesiones, caché y colas de base de datos |
| `php artisan migrate:status` | 10 migraciones ya aplicadas en SQLite. Consulta de solo lectura |
| `php artisan route:list --except-vendor` | 23 rutas de aplicación. Tienda, autenticación, cuenta y configuración |
| `php artisan route:list --path=admin` | Sin ruta registrada. Chromium verificó HTTP 404 como invitado y autenticado |
| `php artisan route:list --path=livewire` | Nueve rutas del paquete. Asset `livewire.js` con HTTP 200 |
| `npm run typecheck`, `npm run lint`, `npm run build` | Pasaron. Vite conserva dos referencias de imágenes públicas que se verificaron con HTTP 200 |
| HTTP local | Tienda 200, login 200 y cliente Vite 200 |
| Chromium, `docs/testing/browser-sdd02.cjs` | Pasó la comprobación visual con contexto aislado y una cuenta sintética. Sin excepciones JS o respuestas HTTP de error en los flujos esperados |
| `php artisan test`, pruebas de autorización y relaciones | Bloqueados por el alcance del AGENTS.md vigente. Las suites del starter se conservaron |
| MariaDB y carga masiva | Verificación histórica de 10 migraciones e índice FULLTEXT en MariaDB 10.11.16. En esta iteración se consultó la versión del binario y las escuchas locales. Pruebas de relaciones, rollback y benchmark siguen pendientes |

## Recorridos automatizados

- Carrito de muestra, alta de dos artículos, alta repetida, cantidades, límite de nueve, eliminación y vaciado.
- Bloqueo de producto agotado, limpieza de almacenamiento inválido, navegación Inertia, recarga y persistencia de moneda.
- Carga y error de disponibilidad explícitamente simulados, comprobación del resumen de checkout y pago deshabilitado.
- Foco inicial del diálogo, cierre con Escape y devolución del foco al disparador.
- Registro sintético, redirección de invitado, perfil Laravel, preferencia de moneda, detalle de pedido y direcciones de muestra.
- Filtros de catálogo administrativo, fechas de pedidos, estados vacío/carga/error, alta/edición en vista previa y scroll horizontal de tablas. Comprobación responsive a 320, 768 y 1440 px.
- Ausencia de ruta `/admin`. El resultado no valida roles ni acceso autorizado futuro.

Ejecutar desde el proyecto con Laravel en `127.0.0.1:8000` y Vite en desarrollo. El script usa `playwright-core` y Chromium instalados en un directorio de herramientas. Configurar `SDD_PLAYWRIGHT_MODULE` con la ruta absoluta a `playwright-core` si está instalado fuera del proyecto. El registro usa la base local de desarrollo y un identificador `@example.test` sintético.

```powershell
node docs/testing/browser-sdd02.cjs
```

## Capturas

- [Carrito a 320 px](../evidence/sdd-02/carrito-320.png)
- [Aviso de disponibilidad](../evidence/sdd-02/carrito-disponibilidad-320.png)
- [Administración de productos en escritorio](../evidence/sdd-02/administracion-productos-desktop.png)
- [Administración de precios a 320 px](../evidence/sdd-02/administracion-320.png)
