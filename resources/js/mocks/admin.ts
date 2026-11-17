import { formatPrice, products, type Product } from '@/mocks/store';

interface PreviewRow {
    id: string;
    cells: string[];
    status: string;
    occurredOn?: string;
}

export interface PreviewDataset {
    title: string;
    description: string;
    columns: string[];
    rows: PreviewRow[];
}

const sampleStock: Record<Product['id'], number> = { 'MO-035': 32, 'CA-002': 7, 'HI-750': 24, 'IL-300': 0 };

export const adminTables = {
    products: {
        title: 'Productos',
        description: 'Fichas, imágenes, atributos y publicación de catálogo.',
        columns: ['SKU', 'Producto', 'Categoría', 'Marca', 'Precio USD', 'Precio MXN', 'Stock demo', 'Disponibilidad'],
        rows: products.map((product) => ({
            id: product.id,
            cells: [
                product.id,
                product.name,
                product.category,
                product.brand,
                formatPrice(product.prices.USD, 'USD'),
                formatPrice(product.prices.MXN, 'MXN'),
                String(sampleStock[product.id]),
                product.available ? 'Disponible' : 'Agotado',
            ],
            status: 'Publicado',
        })),
    },
    catalog: {
        title: 'Categorías y marcas',
        description: 'Organización del catálogo y atributos disponibles.',
        columns: ['Tipo', 'Nombre', 'Referencia'],
        rows: [
            { id: 'cat', cells: ['Categoría', 'Campamento', 'cat-campamento'], status: 'Activa' },
            { id: 'brand', cells: ['Marca', 'Refugio', 'mar-refugio'], status: 'Activa' },
            { id: 'attribute', cells: ['Atributo', 'Capacidad', 'L'], status: 'Filtrable' },
        ],
    },
    inventory: {
        title: 'Inventario y lotes',
        description: 'Existencias, almacenes, lotes, movimientos y reservas de ejemplo.',
        columns: ['Tipo', 'Referencia', 'Almacén', 'Unidades', 'Costo histórico'],
        rows: [
            { id: 'stock', cells: ['Existencia', 'MO-035', 'Central demo', '32', 'MXN 910.0000'], status: 'Disponible' },
            { id: 'lot', cells: ['Lote', 'LOT-DEMO-04', 'Central demo', '12', 'MXN 892.0000'], status: 'Stock bajo' },
            { id: 'move', cells: ['Movimiento', 'MOV-DEMO-08', 'Central demo', '−2', 'MXN 892.0000'], status: 'Salida demo' },
            { id: 'reserve', cells: ['Reserva', 'RSV-DEMO-02', 'Central demo', '2', 'Por confirmar'], status: 'Retenida demo' },
        ],
    },
    suppliers: {
        title: 'Proveedores',
        description: 'Productos, moneda de compra, costos cotizados e historial de compra.',
        columns: ['Proveedor', 'Producto', 'Moneda de compra', 'Costo cotizado', 'Última compra'],
        rows: [
            {
                id: 'north',
                cells: ['Suministros Norte (demo)', 'Mochila Sierra 35 L', 'MXN', 'MXN 890.0000', '12 nov 2026 · Demo'],
                status: 'Preferente',
            },
            {
                id: 'mountain',
                cells: ['Equipo Montaña (demo)', 'Botella Bruma 750 ml', 'USD', 'USD 15.5000', 'Sin historial'],
                status: 'Alternativo',
            },
        ],
    },
    pricing: {
        title: 'Precios',
        description: 'Ejemplos de precios publicados y costos por lote. Los importes ilustran el diseño.',
        columns: [
            'Producto',
            'Precio base',
            'Política',
            'Costo histórico',
            'Reposición',
            'Margen mín.',
            'Piso protegido',
            'MXN publicado',
            'USD publicado',
            'Último cambio',
        ],
        rows: [
            {
                id: 'HI-750',
                cells: [
                    'Botella Bruma 750 ml',
                    'USD 30.0000',
                    'Dinámica protegida',
                    'MXN 278.5000',
                    'MXN 285.2500',
                    '10 %',
                    'MXN 329.00',
                    'MXN 649.00',
                    'USD 37.00',
                    '12 nov · Demo',
                ],
                status: 'Publicación demo',
            },
        ],
    },
    exchange: {
        title: 'Banxico',
        description: 'Vista del FIX, histórico y alertas. El valor vigente y la última consulta real permanecen sin comprobar.',
        columns: ['Serie', 'Fecha del dato', 'FIX', 'Última consulta', 'Alerta'],
        rows: [
            {
                id: 'latest',
                cells: ['SF43718 · Demo', '13 nov 2026', '18.00000000 MXN por USD', '13 nov · Demo', 'Ejemplo reciente'],
                status: 'Completada demo',
            },
            {
                id: 'stale',
                cells: ['SF43718 · Demo', '10 nov 2026', '17.85000000 MXN por USD', '12 nov · Demo', 'Dato antiguo'],
                status: 'Atrasada demo',
            },
        ],
    },
    orders: {
        title: 'Pedidos y Stripe',
        description: 'Pedidos, pagos e historial de eventos. Las referencias son ficticias.',
        columns: ['Tipo', 'Referencia', 'Importe', 'Fecha', 'Procesamiento'],
        rows: [
            {
                id: 'order',
                cells: ['Pedido', 'RF-1042', 'MXN 1,899.00', '16 nov 2026', 'En preparación'],
                status: 'Pendiente demo',
                occurredOn: '2026-11-16',
            },
            {
                id: 'payment',
                cells: ['Pago', 'PAGO-DEMO-01', 'MXN 649.00', '11 nov 2026', 'Esperando validación'],
                status: 'Por conciliar demo',
                occurredOn: '2026-11-11',
            },
            {
                id: 'event',
                cells: ['Evento', 'EVENTO-DEMO-16', 'No aplica', '11 nov 2026', 'Error de procesamiento ilustrativo'],
                status: 'Error demo',
                occurredOn: '2026-11-11',
            },
        ],
    },
    users: {
        title: 'Usuarios y permisos',
        description: 'Ejemplos de roles, permisos efectivos y auditoría sin acceso a cuentas reales.',
        columns: ['Tipo', 'Sujeto', 'Rol', 'Permiso o cambio'],
        rows: [
            { id: 'example-user', cells: ['Usuario de muestra', 'Cliente demo', 'customer', 'Recursos propios (propuesto)'], status: 'Lectura demo' },
            {
                id: 'example-admin',
                cells: ['Operador de muestra', 'Administrador demo', 'admin', 'products.view (propuesto)'],
                status: 'Lectura demo',
            },
            {
                id: 'example-audit',
                cells: ['Auditoría ficticia', 'Registro de ejemplo', 'Sin asignación', 'Revisión de permisos'],
                status: 'Vista previa',
            },
        ],
    },
    sync: {
        title: 'Sincronizaciones',
        description: 'Ejemplos de estados operativos, sin mediciones de servicios reales.',
        columns: ['Servicio', 'Proceso', 'Última comprobación', 'Incidencia'],
        rows: [
            { id: 'redis', cells: ['Redis', 'Cola', 'Sin comprobar', 'Sin incidencia en la muestra'], status: 'Disponible · Demo' },
            {
                id: 'elastic',
                cells: ['Elasticsearch', 'Índice de catálogo', 'Sin comprobar', 'Retraso en cola de ejemplo'],
                status: 'Degradado · Demo',
            },
            {
                id: 'banxico',
                cells: ['Banxico', 'Consulta FIX', 'Sin comprobar', 'Servicio de ejemplo fuera de línea'],
                status: 'Fuera de línea · Demo',
            },
        ],
    },
} satisfies Record<string, PreviewDataset>;

export type AdminTableKey = keyof typeof adminTables;
