import test from 'node:test';
import assert from 'node:assert/strict';
import { demoResponse, periodRows, fixture } from '../../resources/js/dashboard-data.js';

const request = { draw: 3, start: 0, length: 10, search: { value: '' } };

test('la muestra cabe en una sola página y devuelve el contrato de DataTables', () => {
 for (const kind of ['products', 'orders']) {
  const response = demoResponse(kind, request);
  assert.equal(response.draw, 3);
  assert.equal(response.recordsTotal, fixture[kind].data.length);
  assert.ok(response.recordsTotal < request.length);
  assert.equal(response.data.length, response.recordsFiltered);
  // No se simula una segunda página repitiendo los registros de la primera.
  assert.deepEqual(demoResponse(kind, { ...request, start: 10 }).data, []);
 }
});

test('los filtros se combinan y distinguen total de registros filtrados', () => {
 const response = demoResponse('products', { ...request, search: { value: 'mochila' } }, { category: 'Mochilas', status: 'Stock bajo' });
 assert.equal(response.recordsTotal, 8);
 assert.equal(response.recordsFiltered, 1);
 assert.equal(response.data[0].sku, 'MO-018');
 assert.equal(demoResponse('orders', request, { status: 'Inexistente' }).recordsFiltered, 0);
});

test('ordenar una muestra no modifica el JSON original', () => {
 const before = JSON.stringify(fixture.products.data);
 const response = demoResponse('products', { ...request, columns: [{ data: 'price' }], order: [{ column: 0, dir: 'desc' }] });
 assert.equal(response.data[0].price, 3499);
 assert.equal(JSON.stringify(fixture.products.data), before);
});

test('el periodo incluye los extremos y no inventa datos para fechas vacías o invertidas', () => {
 assert.equal(periodRows('2026-11-11', '2026-11-17').length, 7);
 assert.equal(periodRows('2026-11-17', '2026-11-17')[0].sales, 17080);
 assert.deepEqual(periodRows('2026-11-17', '2026-11-11'), []);
 assert.deepEqual(periodRows('2027-01-01', '2027-01-31'), []);
});
