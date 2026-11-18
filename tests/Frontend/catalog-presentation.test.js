import test from 'node:test';
import assert from 'node:assert/strict';
import { filterCatalog, externalProductImage } from '../../resources/js/catalog-presentation.js';

const products = [
    { id: '1', name: 'Botella térmica', category: 'Hidratación', brand: 'Refugio', available: true, prices: { MXN: '400.00', USD: '25.00' } },
    { id: '2', name: 'Mochila Sierra', category: 'Mochilas', brand: 'Cumbre', available: false, prices: { MXN: '900.00', USD: '45.00' } },
    { id: '3', name: 'Botella ligera', category: 'Hidratación', brand: 'Cumbre', available: true, prices: { MXN: '250.00', USD: '15.00' } },
];
test('la búsqueda encuentra palabras con y sin acentos', () => {
    assert.deepEqual(filterCatalog(products, { query: 'termica' }, 'MXN').map(p => p.id), ['1']);
    assert.equal(filterCatalog(products, { query: 'hidratacion' }, 'MXN').length, 2);
});
test('combina filtros de marca, categoría, disponibilidad y presupuesto', () => {
    assert.deepEqual(filterCatalog(products, { category: 'Hidratación', brand: 'Cumbre', stock: true, maximum: '300' }, 'MXN').map(p => p.id), ['3']);
    assert.equal(filterCatalog(products, { query: 'inexistente' }, 'MXN').length, 0);
});
test('ordena precios numéricos en ambas monedas sin mutar el catálogo', () => {
    assert.deepEqual(filterCatalog(products, { sort: 'asc' }, 'MXN').map(p => p.id), ['3','1','2']);
    assert.deepEqual(filterCatalog(products, { sort: 'desc' }, 'USD').map(p => p.id), ['2','1','3']);
    assert.deepEqual(products.map(p => p.id), ['1','2','3']);
});
test('solo acepta URLs externas HTTP y HTTPS para imágenes de producto', () => {
    assert.equal(externalProductImage({ image_url: 'https://example.com/photo.jpg' }), 'https://example.com/photo.jpg');
    for (const image_url of ['javascript:alert(1)', 'data:image/svg+xml,x', 'file:///secret', '/internal.png', 'invalid']) assert.equal(externalProductImage({ image_url }), '');
    assert.equal(externalProductImage(null), '');
});
