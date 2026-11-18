@props(['title' => 'El pulso de tu tienda', 'ranking' => false])
<section class="ro-dashboard" x-data="refugioAnalytics(@js($ranking ? 'top' : 'overview'))">
 <header class="ro-hero"><div><span class="ro-demo"><span aria-hidden="true"></span> Administración</span><h1>{{ $title }}</h1><p>Una mirada a las ventas, los pedidos y el equipo que más sale al camino.</p></div><a class="ro-button ro-button-light" href="{{ route('store.catalog') }}">Visitar la tienda <span aria-hidden="true">↗</span></a></header>
 <div class="ro-period"><div><strong>Resumen del periodo</strong><p>Ventas pagadas del periodo seleccionado.</p></div><div class="ro-date-fields"><label>Desde<input type="date" x-model="from" @change="refresh()"></label><label>Hasta<input type="date" x-model="until" @change="refresh()"></label><label>Moneda<select x-model="currency" @change="refresh()"><option>MXN</option><option>USD</option></select></label><button type="button" class="ro-button" @click="reset()">Restablecer</button></div></div>
 <p class="ro-error" role="alert" x-show="invalid" x-cloak>La fecha inicial debe ser anterior o igual a la fecha final.</p>
 <p class="ro-error" role="alert" x-show="error" x-cloak><span x-text="error"></span> <button type="button" @click="refresh()">Reintentar</button></p>
 <p role="status" class="ro-footnote" x-show="loading">Consultando el reporte…</p>
 @if($ranking)
 <x-admin.top-products-panel />
 @else
 <div class="ro-metrics" aria-live="polite">
  <article><span>Ventas del periodo</span><strong x-text="money(sales)"></strong><small>Ventas pagadas</small></article>
  <article><span>Ganancia bruta</span><strong x-text="profit === null ? 'Sin costo completo' : money(profit)"></strong><small>Sin IVA ni envío; descuenta el costo histórico</small></article>
  <article><span>Pedidos</span><strong x-text="orders"></strong><small>Pedidos pagados del periodo</small></article>
  <article><span>Ticket promedio</span><strong x-text="money(orders ? sales / orders : 0)"></strong><small>Promedio de ventas pagadas</small></article>
 </div>
 <div class="ro-chart-grid">
  <section class="ro-panel ro-chart-panel" aria-labelledby="ro-chart-heading"><header class="ro-section-heading"><div><h2 id="ro-chart-heading">Cada día cuenta</h2><p>Ventas cobradas y ganancia bruta diaria</p></div><div class="ro-toggle" aria-label="Serie de la gráfica"><button type="button" :aria-pressed="metric === 'sales'" @click="metric = 'sales'">Ventas</button><button type="button" :aria-pressed="metric === 'profit'" @click="metric = 'profit'">Ganancias</button></div></header>
   <p class="ro-footnote" x-show="missingCosts" x-cloak>Hay <span x-text="missingCosts"></span> pedidos sin costo o tipo de cambio histórico. Su día se muestra sin ganancia calculada.</p>
   <p class="ro-empty" x-show="!days.length">No hay ventas pagadas en este periodo y moneda.</p>
   <div class="ro-bars" x-show="days.length" role="img" :aria-label="(metric === 'sales' ? 'Ventas' : 'Ganancias') + ' en ' + currency + '. Datos detallados debajo.'">
    <template x-for="day in days" :key="day.date"><div class="ro-bar-column"><span class="ro-bar-number" x-text="day[metric] === null ? 'Sin costo' : money(day[metric])"></span><div class="ro-bar-track"><div class="ro-bar" :class="{ 'ro-bar-profit': metric === 'profit', 'ro-bar-loss': day[metric] < 0 }" :style="{height: (Math.abs(day[metric] ?? 0) / maximum * 100) + '%'}"></div></div><span class="ro-bar-date" x-text="dayLabel(day.date)"></span></div></template>
   </div>
   <details class="ro-chart-data"><summary>Ver datos de la gráfica</summary><div class="ro-table-region"><table><caption class="sr-only">Ventas y pedidos pagados del periodo</caption><thead><tr><th scope="col">Fecha</th><th scope="col">Ventas</th><th scope="col">Ganancia bruta</th><th scope="col">Pedidos</th></tr></thead><tbody><template x-for="day in days" :key="day.date"><tr><th scope="row" x-text="dayLabel(day.date)"></th><td x-text="money(day.sales)"></td><td x-text="day.profit === null ? 'Sin costo completo' : money(day.profit)"></td><td x-text="day.orders"></td></tr></template></tbody></table></div></details>
  </section>
  <x-admin.top-products-panel />
 </div>
 <x-admin.order-list />
 @endif
</section>
