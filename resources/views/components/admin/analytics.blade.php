@props(['title' => 'El pulso de tu tienda', 'ranking' => false])
<section class="ro-dashboard" x-data="refugioAnalytics">
 <header class="ro-hero"><div><span class="ro-demo"><span aria-hidden="true"></span> Vista de demostración</span><h1>{{ $title }}</h1><p>Una mirada a las ventas, los pedidos y el equipo que más sale al camino.</p></div><a class="ro-button ro-button-light" href="{{ route('store.catalog') }}">Visitar la tienda <span aria-hidden="true">↗</span></a></header>
 <div class="ro-period"><div><strong>Resumen del periodo</strong><p>Todos los datos son ficticios. Moneda: MXN.</p></div><div class="ro-date-fields"><label>Desde<input type="date" x-model="from" min="2026-11-11" max="2026-11-17"></label><label>Hasta<input type="date" x-model="until" min="2026-11-11" max="2026-11-17"></label><button type="button" class="ro-button" @click="reset()">Restablecer</button></div></div>
 <p class="ro-error" role="alert" x-show="invalid" x-cloak>La fecha inicial debe ser anterior o igual a la fecha final.</p>
 <div class="ro-metrics" aria-live="polite">
  <article><span>Ventas del periodo</span><strong x-text="money(sales)"></strong><small>Ingresos de ejemplo</small></article>
  <article><span>Ganancia estimada</span><strong x-text="money(profit)"></strong><small>Cifra ficticia, sin cálculo contable</small></article>
  <article><span>Pedidos</span><strong x-text="orders"></strong><small>Pedidos de ejemplo del periodo</small></article>
  <article><span>Ticket promedio</span><strong x-text="money(orders ? sales / orders : 0)"></strong><small>Promedio ilustrativo por pedido</small></article>
 </div>
 <div class="ro-chart-grid">
  <section class="ro-panel ro-chart-panel" aria-labelledby="ro-chart-heading"><header class="ro-section-heading"><div><h2 id="ro-chart-heading">Cada día cuenta</h2><p>Comparativa diaria · MXN · Datos ficticios</p></div><div class="ro-toggle" aria-label="Serie de la gráfica"><button type="button" :aria-pressed="metric === 'sales'" @click="metric = 'sales'">Ventas</button><button type="button" :aria-pressed="metric === 'profit'" @click="metric = 'profit'">Ganancias</button></div></header>
   <p class="ro-empty" x-show="!days.length">No hay datos de ejemplo para este periodo. Prueba del 11 al 17 de noviembre.</p>
   <div class="ro-bars" x-show="days.length" role="img" :aria-label="(metric === 'sales' ? 'Ventas' : 'Ganancias') + ' de ejemplo en pesos mexicanos. Datos detallados debajo.'">
    <template x-for="day in days" :key="day.date"><div class="ro-bar-column"><span class="ro-bar-number" x-text="day[metric].toLocaleString('es-MX')"></span><div class="ro-bar-track"><div class="ro-bar" :class="{ 'ro-bar-profit': metric === 'profit' }" :style="{height: (day[metric] / maximum * 100) + '%'}"></div></div><span class="ro-bar-date" x-text="dayLabel(day.date)"></span></div></template>
   </div>
   <details class="ro-chart-data"><summary>Ver datos de la gráfica</summary><div class="ro-table-region"><table><caption class="sr-only">Ventas, ganancias y pedidos del periodo, datos ficticios</caption><thead><tr><th scope="col">Fecha</th><th scope="col">Ventas MXN</th><th scope="col">Ganancias MXN</th><th scope="col">Pedidos</th></tr></thead><tbody><template x-for="day in days" :key="day.date"><tr><th scope="row" x-text="dayLabel(day.date)"></th><td x-text="money(day.sales)"></td><td x-text="money(day.profit)"></td><td x-text="day.orders"></td></tr></template></tbody></table></div></details>
  </section>
  <section class="ro-panel ro-ranking"><header class="ro-section-heading"><div><h2>Los favoritos del camino</h2><p>Top 3 ilustrativo · 11–17 nov. 2026</p></div></header><p class="ro-footnote">Muestra fija de la semana; independiente del filtro de fechas.</p><ol><template x-for="(product, index) in top" :key="product.name"><li><span class="ro-rank" x-text="index + 1"></span><div><strong x-text="product.name"></strong><small x-text="product.category"></small><div class="ro-rank-track"><span :style="{width: (product.units / top[0].units * 100) + '%'}"></span></div></div><span class="ro-units"><strong x-text="product.units"></strong><small>unidades</small></span></li></template></ol><a class="ro-text-link" href="{{ route('admin.reports.top-products') }}">Ver reporte de productos</a></section>
 </div>
 @unless($ranking)<x-admin.order-list />@endunless
</section>
