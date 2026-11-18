// Estimates only. The server must independently recalculate every amount and check stock.
export function cents(value) {
    const text = String(value);
    if (!/^\d+(?:\.\d{1,2})?$/.test(text)) throw new Error('Importe de presentación inválido.');
    const [whole, fraction = ''] = text.split('.');
    const result = Number(whole) * 100 + Number(fraction.padEnd(2, '0'));
    if (!Number.isSafeInteger(result)) throw new Error('Importe fuera de rango.');
    return result;
}

export function estimate(products, quantities, currency) {
    if (!['MXN', 'USD'].includes(currency)) throw new Error('Moneda inválida.');
    const subtotal = products.reduce((sum, product) => {
        const quantity = quantities[product.id] ?? 0;
        if (!Number.isInteger(quantity) || quantity < 0 || quantity > 99) throw new Error('Cantidad inválida.');
        return sum + cents(product.prices[currency]) * quantity;
    }, 0);
    if (!Number.isSafeInteger(subtotal) || subtotal > Number.MAX_SAFE_INTEGER / 116) throw new Error('Total fuera de rango.');
    const tax = Math.round((subtotal * 16) / 100);
    return { subtotal: subtotal / 100, tax: tax / 100, total: (subtotal + tax) / 100 };
}

export function checkoutItems(products, quantities) {
    const selected = products.filter(product => (quantities[product.id] ?? 0) > 0);
    if (selected.length === 0) throw new Error('Agrega un producto antes de continuar.');
    const seen = new Set();
    return selected.map(product => {
        const quantity = quantities[product.id];
        if (!Number.isSafeInteger(product.product_id) || product.product_id <= 0) throw new Error('El catálogo de ejemplo todavía no tiene IDs reales de la base de datos.');
        if (seen.has(product.product_id)) throw new Error('Producto duplicado en el catálogo.');
        seen.add(product.product_id);
        if (!Number.isInteger(quantity) || quantity < 1 || quantity > 99) throw new Error('Cantidad inválida.');
        return { product_id: product.product_id, quantity };
    });
}
