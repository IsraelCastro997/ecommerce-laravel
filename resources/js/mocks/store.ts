export type Currency = 'MXN' | 'USD';

export const categories = ['Mochilas', 'Campamento', 'Hidratación', 'Iluminación'] as const;

export const products = [
    {
        id: 'MO-035',
        name: 'Mochila Sierra 35 L',
        category: 'Mochilas',
        brand: 'Refugio',
        origin: 'México',
        detail: 'Mochila de 35 litros con respaldo ventilado y cubierta para lluvia.',
        prices: { MXN: 1899, USD: 109 },
        available: true,
        image: 'top left',
    },
    {
        id: 'CA-002',
        name: 'Tienda Nómada 2P',
        category: 'Campamento',
        brand: 'Refugio',
        origin: 'México',
        detail: 'Tienda de doble pared para dos personas con vestíbulo frontal.',
        prices: { MXN: 3499, USD: 199 },
        available: true,
        image: 'top right',
    },
    {
        id: 'HI-750',
        name: 'Botella Bruma 750 ml',
        category: 'Hidratación',
        brand: 'Cumbre',
        origin: 'China',
        detail: 'Botella de acero inoxidable con aislamiento y tapa de rosca.',
        prices: { MXN: 649, USD: 37 },
        available: true,
        image: 'bottom left',
    },
    {
        id: 'IL-300',
        name: 'Linterna Lumbre 300',
        category: 'Iluminación',
        brand: 'Cumbre',
        origin: 'China',
        detail: 'Linterna frontal ligera con cinta ajustable y tres niveles de luz.',
        prices: { MXN: 799, USD: 45 },
        available: false,
        image: 'bottom right',
    },
] as const;

export type Product = (typeof products)[number];

export function formatPrice(amount: number, currency: Currency) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency, currencyDisplay: 'code' }).format(amount);
}

export const dashboard = {
    metrics: [
        { label: 'Ventas del periodo', value: 'MXN 124,580', note: 'Últimos 7 días' },
        { label: 'Pedidos', value: '248', note: 'Últimos 7 días' },
        { label: 'Productos activos', value: '1,284', note: 'Catálogo de ejemplo' },
        { label: 'Stock bajo', value: '16', note: 'Productos por revisar' },
    ],
    sales: [12300, 16800, 14700, 20100, 18400, 25200, 17080],
    orders: [
        { id: 'RF-1042', date: '16 nov 2026', status: 'Preparando', total: 'MXN 1,899.00' },
        { id: 'RF-1038', date: '11 nov 2026', status: 'Entregado', total: 'MXN 649.00' },
    ],
};
