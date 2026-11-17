# AGENTS.md — Alcance estricto de agentes de IA

## Regla no negociable
Este proyecto permite IA para tareas de presentación frontend y para la excepción de modelos Eloquent y migraciones indicada abajo. Fuera de esa excepción, un agente no puede programar, modificar, autocompletar, refactorizar, depurar ni generar tests de backend, tampoco mediante scripts o comandos de terceros. No convertir el trabajo backend en “frontend” por el hecho de estar ligado a una página o a Livewire.

## Excepción expresa para modelos Eloquent y migraciones
El responsable autoriza crear, corregir y completar los modelos Eloquent en `app/Models/**` y crear, corregir y completar migraciones en `database/migrations/**` dentro de este proyecto. Se permiten tablas, claves, índices, relaciones, casts, timestamps, soft deletes y protección de asignación masiva conforme al esquema acordado. Se permite usar Artisan y verificar sintaxis, esquema y migraciones con fixtures controlados en bases desechables. La autorización de modelos incluye User en lo relativo al modelo; cualquier configuración de login continúa a cargo del responsable. Los controladores, servicios, reglas de negocio, acciones comerciales Livewire, factories, seeders y pruebas backend continúan fuera del alcance. El responsable autoriza aplicar las migraciones pendientes a su base local configurada y ejecutar verificaciones en ella. Los datos actuales deben preservarse. Las operaciones destructivas siguen requiriendo autorización expresa.

## Zonas frontend permitidas (además de la excepción de modelos y migraciones)
- Componentes visuales Vue (`resources/js/**/*.vue`), composables puramente de presentación sin reglas de negocio, CSS/Tailwind (`resources/css/**`), assets públicos e iconografía.
- Vistas Blade de **solo marcado/presentación** (`resources/views/**`) si no contienen reglas de negocio PHP, consultas, autorizaciones, acciones Livewire ni interacciones de servidor.
- Diseño accesible/responsive, estados de carga/error basados en contratos existentes, microinteracciones y textos de interfaz.
- Tests frontend **puramente visuales** si el responsable los permite y no modifican backend.

## Zonas prohibidas para agentes
- `app/**` fuera de la excepción expresa para modelos Eloquent, `bootstrap/**`, `config/**`, `database/**` fuera de la excepción expresa de migraciones, `routes/**`, `tests/Feature/**`, `tests/Unit/**`, `artisan`, `composer.json`, `composer.lock`, infraestructura/CI que ejecute migraciones, y cualquier archivo PHP de lógica.
- Livewire: **clases PHP, propiedades, listeners, métodos, validaciones, policies y acciones** prohibidos. Solo marcado visual de vistas, sin alterar semántica de bindings o seguridad.
- Stripe, Banxico, Redis, Elasticsearch: credenciales, SDK backend, controladores, jobs, eventos, permisos, configuración de servidores o endpoints prohibidos.
- Fuera de las migraciones autorizadas, cambios de esquema/SQL, consultas comerciales, seeder/factory PHP, reglas de precio, inventario, checkout, impuestos y promociones prohibidos.
- No generar ejemplos de código backend ni sugerir comandos que modifiquen el backend dentro de un PR automático.

## Contratos del frontend
1. El backend y sus contratos de propiedades/errores son dados por el desarrollador humano; el agente no los inventa como si existieran.
2. En ausencia de contrato, utilizar datos mock **claramente etiquetados y aislados en archivos frontend**; no simular pagos reales ni exposición de claves.
3. No calcular ni confiar en importes del cliente; renderizar importes emitidos por servidor y marcar precios estimados cuando proceda.
4. No editar archivos fuera del alcance expresamente autorizado, aunque sea necesario para completar una pantalla.
5. Si una tarea requiere modificar rutas, controladores, componentes PHP Livewire o configuraciones, o excede la excepción de modelos y migraciones: **detenerse, describir el bloqueo y entregar al humano una especificación funcional, sin escribir el código**.
6. No ejecutar comandos destructivos, migraciones, seeders, `composer`, deploys, cambios en producción ni `git push` sin autorización humana expresa. No exponer secretos o registros personales.

## Checklist previo a cada trabajo
- Confirmar rutas que se van a modificar y su pertenencia al frontend.
- Validar diseño solicitado, contrato de entrada/salida y estados visuales.
- Inventariar archivos modificados antes y después; no tocar backend incidentalmente.
- Ejecutar lint/test frontend ya existentes si son seguros, sin alterar backend. Para modelos y migraciones se permiten las verificaciones descritas en la excepción.
- Entregar: resumen visual, archivos cambiados, evidencia de responsive/accesibilidad, pendientes humanos.

## Criterios de calidad del frontend
Accesibilidad WCAG 2.2 AA como objetivo; teclado y focus, etiquetas, feedback de error legible; responsive 320px–desktop; skeletons; tablas con paginación remota y estado vacío; ninguna lista de cientos de miles cargada al navegador; moneda siempre explícita; no mostrar éxito de pago hasta confirmación desde servidor.

## Jerarquía
Las instrucciones humanas específicas pueden reducir el alcance permitido. Para el backend rige únicamente la excepción expresa de modelos Eloquent y migraciones descrita en este archivo. Si hay ambigüedad, tratar el archivo como **prohibido**.
