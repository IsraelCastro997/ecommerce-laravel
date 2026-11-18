const normalized = value => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es-MX');

export function filterCatalog(products, { query = '', category = '', brand = '', stock = false, maximum = '', sort = '' }, currency) {
    const phrase = normalized(query.trim());
    const visible = products.filter(product =>
        (!phrase || normalized([product.name, product.category, product.brand, product.sku ?? product.id].join(' ')).includes(phrase)) &&
        (!category || product.category === category) &&
        (!brand || product.brand === brand) &&
        (!stock || product.available) &&
        (maximum === '' || Number(product.prices[currency]) <= Number(maximum))
    );
    if (sort) visible.sort((a, b) => (Number(a.prices[currency]) - Number(b.prices[currency])) * (sort === 'asc' ? 1 : -1));
    return visible;
}

export function externalProductImage(product) {
    if (!product || typeof product.image_url !== 'string') return '';
    try {
        const url = new URL(product.image_url);
        return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
    } catch { return ''; }
}
