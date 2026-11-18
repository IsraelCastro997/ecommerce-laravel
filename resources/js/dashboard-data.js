import fixture from './demo/dashboard.json' with { type: 'json' };

// Replace null with a human-provided GET endpoint implementing the DataTables
// server-side protocol. No database IDs or commercial API routes are invented.
export const dashboardEndpoints = { products: null, orders: null };
export { fixture };

// A single, already bounded fixture page. Never slice a full collection here.
export function demoResponse(kind, request, filters = {}) {
 const page = fixture[kind];
 const query = String(request.search?.value || '').toLocaleLowerCase('es');
 const rows = page.data.filter(row =>
  Object.values(row).join(' ').toLocaleLowerCase('es').includes(query)
  && (!filters.category || row.category === filters.category)
  && (!filters.status || row.status === filters.status)
 );
 const field = request.columns?.[request.order?.[0]?.column]?.data;
 const direction = request.order?.[0]?.dir === 'desc' ? -1 : 1;
 if (field) rows.sort((a, b) => direction * (typeof a[field] === 'number'
  ? a[field] - b[field] : String(a[field]).localeCompare(String(b[field]), 'es')));
 return {
  draw: Number(request.draw),
  recordsTotal: page.recordsTotal,
  recordsFiltered: rows.length,
  data: Number(request.start || 0) === 0 ? rows : [],
 };
}

export function periodRows(from, until) {
 return fixture.daily.filter(row => (!from || row.date >= from) && (!until || row.date <= until));
}
