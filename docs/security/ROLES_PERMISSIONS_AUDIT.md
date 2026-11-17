# Auditoría de autorización

| Control | Evidencia | Estado |
|---|---|---|
| Dependencia y compatibilidad | Spatie 8.5.0 en Composer, Laravel 12.72.0, PHP 8.5.12 | Verificado |
| Guard | `config/auth.php` usa guard `web`, driver `session`, proveedor Eloquent de usuarios | Verificado en configuración |
| Esquema | Configuración y migración publicadas. El estado SQLite lista la migración aplicada | Verificado |
| Modelo | `app/Models/User.php` utiliza `HasFactory` y `Notifiable`; carece de `HasRoles` y de relaciones homónimas | Pendiente manual |
| Registro y perfil | Registro crea nombre, correo y contraseña; perfil valida nombre y correo. No hay flujo de asignación de rol en esos archivos | Verificado por lectura |
| Caché | `config/permission.php` usa `spatie.permission.cache`, 24 h, store por defecto. Invalidación con API del paquete por probar | Pendiente manual |
| Administración | No se registró ruta `/admin`. La pestaña de dashboard solo usa datos demo en frontend | Demostración |
| Policies y acciones Livewire | Sin clases, policies ni rutas administrativas específicas encontradas | Bloqueado por alcance |

## Matriz propuesta

El rol `customer` accederá a sus datos propios bajo políticas por recurso. El rol `admin` recibirá el conjunto de permisos que le asigne el operador. Las siguientes son claves propuestas; ninguna se ha configurado como permiso activo.

| Área | Consulta | Cambios de administración |
|---|---|---|
| Catálogo | `products.view` | `products.create`, `products.update`, `products.delete`, `categories.manage` |
| Proveedores | Acceso administrativo al módulo | `suppliers.manage` |
| Inventario | `inventory.view` | `inventory.adjust` |
| Precios y FX | `pricing.view`, `exchange_rates.view` | `pricing.manage`, `exchange_rates.manage` |
| Pedidos y pagos | `orders.view`, `payments.view` | `orders.manage` |
| Usuarios y acceso | `customers.view` | `roles.manage` |
| Operación | Acceso administrativo al módulo | `settings.manage`, `search.manage` |

## Aceptación pendiente del responsable

- Integrar `HasRoles`, activar `customer` y `admin` de manera idempotente y asignar el primer administrador mediante una acción explícita. No provisionar credenciales fijas ni elevar cuentas existentes como efecto del seeder.
- Proteger la ruta, la consulta de cada registro, las exportaciones y cada acción Livewire. Registrar cambios de privilegios y probar invalidación de caché.
- Comprobar invitado, cliente autenticado, administrador autorizado, administrador sin permiso específico, lectura de pedidos ajenos e invocación Livewire sin permisos. Una ruta ausente que responde 404 solo verifica que no hay una entrada publicada.
- En OAuth, la cuenta pública recibe permisos mínimos. La vinculación de identidad requiere una confirmación explícita y el perfil nunca acepta rol o permisos como entrada.

[Instalación oficial de Spatie](https://spatie.be/docs/laravel-permission/v8/installation-laravel) · [Middleware y autorización en Spatie](https://spatie.be/docs/laravel-permission/v8/basic-usage/middleware)
