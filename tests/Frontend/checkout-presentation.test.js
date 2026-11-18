import test from 'node:test';
import assert from 'node:assert/strict';
import { cents, estimate, checkoutItems, stockLimit, nextCartQuantity } from '../../resources/js/checkout-presentation.js';

const products = [{ id: 'sku-demo', product_id: 42, prices: { MXN: '19.99', USD: '1.10' } }];

test('estimación en centavos con IVA del 16 % redondeado sobre el subtotal', () => {
    assert.equal(cents('0.10'), 10);
    assert.deepEqual(estimate(products, { 'sku-demo': 3 }, 'MXN'), { subtotal: 59.97, tax: 9.6, total: 69.57 });
    assert.deepEqual(estimate(products, { 'sku-demo': 3 }, 'USD'), { subtotal: 3.3, tax: 0.53, total: 3.83 });
});
test('el payload contiene exclusivamente ID real y cantidad aunque se alteren precios', () => {
    const tampered = [{ ...products[0], prices: { MXN: 0.01, USD: 0.01 }, total: 0.01, discount: 99 }];
    assert.deepEqual(checkoutItems(tampered, { 'sku-demo': 2 }), [{ product_id: 42, quantity: 2 }]);
});
test('no envía SKUs de demostración como si fueran IDs reales', () => {
    assert.throws(() => checkoutItems([{ ...products[0], product_id: null }], { 'sku-demo': 1 }), /IDs reales/);
    assert.throws(() => checkoutItems(products, {}), /Agrega un producto/);
});
test('rechaza cantidades, importes e IDs fuera del contrato', () => {
    assert.throws(() => checkoutItems(products, { 'sku-demo': 1.5 }), /Cantidad/);
    assert.throws(() => checkoutItems(products, { 'sku-demo': 100 }), /Cantidad/);
    assert.throws(() => checkoutItems([...products, { ...products[0], id: 'other' }], { 'sku-demo': 1, other: 1 }), /duplicado/);
    assert.throws(() => cents('0.001'), /inválido/);
    assert.throws(() => estimate(products, {}, 'EUR'), /Moneda/);
});

test('no envia un pedido parcial si falta un producto de otra pagina', () => {
 assert.throws(() => checkoutItems(products, {'sku-demo':1, 'another-page':2}), /Faltan productos/);
});

test('limita cantidades a stock disponible y permite reducir y eliminar', () => {
 const product={available_quantity:3};
 assert.equal(stockLimit(product),3);
 assert.equal(nextCartQuantity(product,2,5),3);
 assert.equal(nextCartQuantity(product,3,1),3);
 assert.equal(nextCartQuantity(product,3,-1),2);
 assert.equal(nextCartQuantity({available_quantity:0},3,-1),0);
 assert.equal(stockLimit({available_quantity:200}),99);
});
