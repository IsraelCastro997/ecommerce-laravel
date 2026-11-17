# AGENTS.md — E-commerce Laravel 12

## Restricción de autoría
Se permite IA **solo para frontend** y para ejecutar comandos de bootstrap/instalación/arranque previamente autorizados. Está prohibido generar, corregir, modificar, implementar o probar mediante código de IA la lógica de backend (PHP, SQL de negocio, migraciones, seeders, rutas, servicios, controladores, clases Livewire PHP, Stripe backend, OAuth backend, precios, seguridad y roles).

## Autorización operativa concreta
Puedes crear repo Git **local**, instalar starter kit **oficial Laravel 12 Vue/Inertia con auth de sesión integrada**, instalar Livewire oficial, instalar dependencias, generar `.env` local desde `.env.example`, `key:generate`, inspeccionar entorno, ejecutar las migraciones oficiales y migraciones escritas por el humano **solo en base local desechable**, `route:list`, build frontend, iniciar `composer run dev` o `php artisan serve` + `npm run dev`, verificar HTTP y documentar evidencia. Estas acciones son operativas, **no autorización para programar backend**. No ejecutar migraciones IA previas como entrega de autoría humana.

## Auth
Preferir `web` guard y sesiones del starter Laravel para Inertia+Livewire mismo dominio. **Sanctum no es obligatorio**; instalarlo solo por necesidad API futura aprobada. OAuth Google requiere backend humano, aunque el botón visual lo hace el agente. `admin` nunca se infiere del cliente.

## Áreas visuales
`resources/js/**`, `resources/css/**`, assets públicos y Blade puramente de presentación; no editar métodos, atributos de autenticación, bindings o condiciones de seguridad. Crear home, catálogo, filtros, carrito, checkout visual, perfil/mi cuenta, pedidos y mock visual admin con DataTables server-side como contrato. Mock aislado, rotulado, máximo una página de datos; no vender simulación como real.

## Límites
No modificar `app/**`, `routes/**`, `database/**`, `config/**`, `bootstrap/**`, backend PHP, composer scripts de negocio, test PHP o infraestructura. El scaffolding estándar que crea un instalador oficial debe quedar intacto y registrarse. No `migrate:fresh`, `db:wipe`, seed masivo, cobros reales, secretos, `git push` ni deploy. Si hay bloqueo backend, entregar `docs/BACKEND_HANDOFF.md` y seguir en UI.

## Entrega obligatoria
Seguir `SDD_FRONTEND_BOOTSTRAP_ECOMMERCE.md`. Proveer ejecución real verificada de Laravel y Vite, URL, comandos, estado de migraciones, evidencia visual, build, auditoría de archivos y tabla de gates PASS/PARTIAL/BLOCKED en `docs/EXECUTION_REPORT.md`. Si no puede mantenerse el servidor vivo al terminar, indicarlo expresamente.
