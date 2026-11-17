# Auditoría del carrito

| Comprobación | Hito React `22911a2` | SDD-02 |
|---|---|---|
| Fuente de productos | Cuatro fixtures en `resources/js/mocks/store.ts` | Mismos fixtures |
| Estado | `bag` local en `Welcome.tsx` | Hook `useDemoCart` y componente `DemoCart` |
| Navegación y recarga | El estado se perdía al desmontar tienda | Se restaura al navegar con Inertia y al recargar en el mismo navegador |
| Productos y cantidades | Varias líneas, incremento y decremento, límite visual de 9 | Controles conservados, aviso de límite y botón para vaciar |
| Datos persistidos | Ninguno | Solo SKU demo y cantidad bajo `refugio:demo-cart:v1`. La preferencia de moneda usa una clave independiente |
| Stock | Agotado etiquetado y excluido de altas | Fixture sin disponibilidad rechazado también al restaurar el estado; aviso de stock y carga explícitamente simulados |
| Dinero | Precio unitario ilustrativo MXN/USD. Total pendiente | Resumen de líneas e importes unitarios. Subtotal, descuento, IVA, envío y total quedan a confirmar por servidor |
| Checkout | Dirección, envío y cupón de demo. Pago deshabilitado | Correo de muestra y resumen ampliado. La UI conserva el pago deshabilitado |
| Contrato | Handoff en prosa | DTOs propuestos en `resources/js/types/commerce.ts` |

La persistencia es local y compartida por visitas en el mismo perfil del navegador. El JSON malformado se trata como vacío y se ignoran SKU desconocidos, agotados y cantidades inválidas. El navegador nunca conserva correo, dirección, precios ni identificadores de pago mediante este hook. El estado local no se sincroniza entre dispositivos o cuentas.

La cotización futura del servidor debe reemplazar los importes demo y devolver la cantidad autorizada, la vigencia y errores estructurados. La prueba de volumen y el procesamiento de cobros quedan fuera de esta implementación.

[Verificaciones de navegador](../testing/SDD-02-TESTS.md)
