<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/mocks/store';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Activity,
    ArrowUpRight,
    Box,
    CircleDollarSign,
    ClipboardList,
    LayoutDashboard,
    Package,
    Settings,
    ShoppingBag,
    Truck,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage<import('@/types').SharedData>();
const section = ref('Mi cuenta');
const module = ref('Resumen');
const previewState = ref<'empty' | 'loading' | 'error'>('empty');
const buttons = ['Mi cuenta', 'Pedidos', 'Direcciones', 'Administración demo'];
const modules = [
    { label: 'Resumen', icon: LayoutDashboard, detail: '' },
    { label: 'Productos', icon: Package, detail: 'Fichas, variantes y disponibilidad de catálogo.' },
    { label: 'Categorías y marcas', icon: Box, detail: 'Jerarquía de catálogo y marcas de producto.' },
    { label: 'Inventario y lotes', icon: ClipboardList, detail: 'Existencias por almacén, lotes y costos históricos.' },
    { label: 'Proveedores', icon: Truck, detail: 'Relación de proveedores y productos suministrados.' },
    { label: 'Precios y Banxico', icon: CircleDollarSign, detail: 'Políticas de precios y cotizaciones históricas.' },
    { label: 'Usuarios y permisos', icon: Users, detail: 'Roles y permisos asignados por el servidor.' },
    { label: 'Pedidos y Stripe', icon: ShoppingBag, detail: 'Pedidos y estados de cobro confirmados por el servidor.' },
    { label: 'Sincronizaciones', icon: Activity, detail: 'Estado de trabajos, Redis e indexación Elasticsearch.' },
];
const selectedModule = computed(() => modules.find((item) => item.label === module.value));
const days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const metricIcons = [CircleDollarSign, ShoppingBag, Package, Activity];
</script>

<template>
    <Head title="Mi cuenta · Refugio Outdoor" />
    <AppLayout :breadcrumbs="[{ title: 'Mi cuenta', href: '/dashboard' }]">
        <main class="dash">
            <header class="dash-intro">
                <div>
                    <p>REFUGIO OUTDOOR</p>
                    <h1>{{ section === 'Administración demo' ? 'Panel de administración' : section }}</h1>
                    <span>{{
                        section === 'Administración demo' ? 'Datos ilustrativos · Vista de demostración' : 'Hola, ' + page.props.auth.user.name
                    }}</span>
                </div>
                <Link href="/">Volver a la tienda <ArrowUpRight :size="17" /></Link>
            </header>
            <nav class="dash-tabs" aria-label="Secciones de mi cuenta">
                <button v-for="item in buttons" :key="item" type="button" :aria-pressed="section === item" @click="section = item">{{ item }}</button>
            </nav>

            <div v-if="section === 'Mi cuenta'" class="account-grid">
                <section class="dash-card">
                    <Settings />
                    <h2>Perfil y seguridad</h2>
                    <p>Actualiza tu nombre o correo y administra tu contraseña.</p>
                    <Link :href="route('profile.edit')">Editar perfil <ArrowUpRight :size="16" /></Link
                    ><Link :href="route('password.edit')">Cambiar contraseña <ArrowUpRight :size="16" /></Link>
                </section>
                <section class="dash-card">
                    <ShoppingBag />
                    <h2>Pedidos y seguimiento</h2>
                    <p>Consulta una muestra del historial y los detalles de pedido.</p>
                    <button type="button" @click="section = 'Pedidos'">Ver pedidos demo <ArrowUpRight :size="16" /></button>
                </section>
                <section class="dash-card">
                    <Truck />
                    <h2>Direcciones de entrega</h2>
                    <p>Vista previa de las direcciones que se podrán guardar en la cuenta.</p>
                    <button type="button" @click="section = 'Direcciones'">Ver direcciones demo <ArrowUpRight :size="16" /></button>
                </section>
            </div>

            <section v-else-if="section === 'Pedidos'" class="dash-card">
                <h2>Historial de ejemplo</h2>
                <p>Pedidos ficticios para mostrar el diseño.</p>
                <details v-for="order in dashboard.orders" :key="order.id">
                    <summary>{{ order.id }} · {{ order.status }}</summary>
                    <p>{{ order.date }} · {{ order.total }}</p>
                    <p>Seguimiento pendiente de integración.</p>
                </details>
            </section>

            <section v-else-if="section === 'Direcciones'" class="dash-card">
                <h2>Direcciones de ejemplo</h2>
                <p>Cuando se integre el servicio, aquí podrás guardar y editar destinos.</p>
                <p class="dash-empty">Todavía no hay direcciones guardadas en esta vista de demostración.</p>
            </section>

            <div v-else class="admin-grid">
                <nav class="dash-modules dash-card" aria-label="Módulos administrativos de demostración">
                    <button
                        v-for="item in modules"
                        :key="item.label"
                        type="button"
                        :aria-pressed="module === item.label"
                        @click="
                            module = item.label;
                            previewState = 'empty';
                        "
                    >
                        <component :is="item.icon" :size="18" /> {{ item.label }}
                    </button>
                </nav>
                <div v-if="module === 'Resumen'" class="admin-content">
                    <div class="metrics-grid">
                        <article v-for="(metric, index) in dashboard.metrics" :key="metric.label" class="dash-card metric">
                            <div>
                                <p>{{ metric.label }}</p>
                                <component :is="metricIcons[index]" :size="18" />
                            </div>
                            <strong>{{ metric.value }}</strong
                            ><small>{{ metric.note }}</small>
                        </article>
                    </div>
                    <div class="account-grid admin-panels">
                        <section class="dash-card">
                            <h2>Ventas por día</h2>
                            <p>Últimos 7 días · MXN · Demo</p>
                            <div class="sales-chart" aria-hidden="true">
                                <div v-for="(value, index) in dashboard.sales" :key="days[index]">
                                    <span :style="{ height: value / 280 + '%' }"></span><small>{{ days[index] }}</small>
                                </div>
                            </div>
                            <details>
                                <summary>Ver datos de la gráfica</summary>
                                <ul>
                                    <li v-for="(value, index) in dashboard.sales" :key="days[index]">
                                        {{ days[index] }} · MXN {{ value.toLocaleString('es-MX') }}
                                    </li>
                                </ul>
                            </details>
                        </section>
                        <section class="dash-card">
                            <h2>Estado operativo</h2>
                            <dl class="operations">
                                <div>
                                    <dt>Redis</dt>
                                    <dd>Pendiente de conexión</dd>
                                </div>
                                <div>
                                    <dt>Elasticsearch</dt>
                                    <dd>Pendiente de conexión</dd>
                                </div>
                                <div>
                                    <dt>Tipo de cambio</dt>
                                    <dd>Demo · 1 USD = 18.00 MXN</dd>
                                </div>
                                <div>
                                    <dt>Stripe</dt>
                                    <dd>Pendiente de conexión</dd>
                                </div>
                            </dl>
                        </section>
                    </div>
                </div>
                <section v-else class="dash-card">
                    <h2>{{ module }}</h2>
                    <p>{{ selectedModule?.detail }}</p>
                    <label class="state-select"
                        >Estado de demostración
                        <select v-model="previewState">
                            <option value="empty">Vacío</option>
                            <option value="loading">Cargando</option>
                            <option value="error">Error de carga</option>
                        </select>
                    </label>
                    <div v-if="previewState === 'loading'" class="dash-empty" role="status">
                        <p>Cargando datos de ejemplo…</p>
                        <div class="admin-skeleton" aria-hidden="true"><span></span><span></span><span></span></div>
                    </div>
                    <p v-else-if="previewState === 'error'" class="dash-empty" role="alert">
                        No se pudo cargar esta vista de ejemplo. <button type="button" @click="previewState = 'empty'">Volver al estado vacío</button>
                    </p>
                    <p v-else class="dash-empty" role="status">
                        Sin registros en esta demostración. Tablas, filtros y paginación de Laravel pendientes de integración.
                    </p>
                </section>
            </div>
        </main>
    </AppLayout>
</template>

<style scoped>
.dash {
    padding: clamp(0.7rem, 3vw, 2rem);
    color: hsl(var(--foreground));
}
.dash :focus-visible {
    outline: 2px solid #b76f20;
    outline-offset: 3px;
}
.dash-intro {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
    padding: clamp(1rem, 3vw, 2rem);
    border-radius: 0.8rem;
    background: #193d2d;
    color: #fff9ed;
}
.dash-intro p {
    color: #f6cb8a;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.13em;
}
.dash-intro h1 {
    margin: 0.25rem 0;
    font-size: clamp(1.9rem, 4vw, 3.5rem);
    font-weight: 820;
    line-height: 1.08;
    letter-spacing: -0.05em;
}
.dash-intro a,
.dash-card :is(a, button) {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.88rem;
    font-weight: 720;
}
.dash-intro a:hover,
.dash-card :is(a, button):hover {
    text-decoration: underline;
}
.dash-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 1rem 0;
}
.dash-tabs button,
.dash-modules button {
    padding: 0.5rem 0.75rem;
    text-align: left;
    border-radius: 0.35rem;
}
.dash-tabs button {
    border: 1px solid hsl(var(--border));
}
.dash-tabs button[aria-pressed='true'],
.dash-modules button[aria-pressed='true'] {
    color: #fff9ed;
    background: #28523d;
}
.account-grid,
.metrics-grid {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 12rem), 1fr));
}
.dash-card {
    min-width: 0;
    padding: 1rem;
    border: 1px solid hsl(var(--border));
    background: hsl(var(--card));
    border-radius: 0.6rem;
}
.dash-card h2 {
    margin: 0.3rem 0;
    font-size: 1.1rem;
    font-weight: 800;
}
.dash-card p {
    font-size: 0.87rem;
    color: hsl(var(--muted-foreground));
}
.dash-card :is(a, button, details) {
    margin-top: 0.55rem;
}
.dash-card a {
    display: flex;
    width: fit-content;
}
.dash-card summary {
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 720;
}
.dash-empty {
    margin-top: 1rem;
    padding: 1rem;
    border: 1px dashed hsl(var(--border));
    border-radius: 0.4rem;
}
.state-select {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.6rem;
    margin-top: 0.9rem;
    font-size: 0.85rem;
}
.state-select select {
    max-width: 100%;
    border-color: hsl(var(--border));
    border-radius: 0.35rem;
    background-color: hsl(var(--card));
    color: inherit;
}
.admin-skeleton {
    display: grid;
    gap: 0.5rem;
    margin-top: 0.65rem;
}
.admin-skeleton span {
    height: 0.8rem;
    border-radius: 0.25rem;
    background: hsl(var(--muted));
}
.admin-skeleton span:nth-child(2) {
    width: 75%;
}
.admin-skeleton span:nth-child(3) {
    width: 55%;
}
.admin-grid {
    display: grid;
    grid-template-columns: 13rem minmax(0, 1fr);
    gap: 0.75rem;
    align-items: start;
}
.dash-modules {
    display: grid;
    padding: 0.5rem;
}
.dash-modules button {
    min-height: 2.5rem;
    margin: 0;
}
.dash-modules svg {
    flex-shrink: 0;
}
.metric div,
.operations div {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}
.metric svg {
    flex-shrink: 0;
}
.metric strong {
    display: block;
    margin: 0.35rem 0;
    font-size: 1.3rem;
    font-weight: 850;
}
.metric small {
    color: hsl(var(--muted-foreground));
}
.admin-panels {
    margin-top: 0.75rem;
}
.sales-chart {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 0.4rem;
    height: 9rem;
    margin-top: 1rem;
}
.sales-chart div {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: end;
    gap: 0.3rem;
}
.sales-chart span {
    width: 75%;
    border-radius: 0.25rem 0.25rem 0 0;
    background: #54866a;
}
.operations {
    margin-top: 0.8rem;
}
.operations div {
    flex-wrap: wrap;
    border-bottom: 1px solid hsl(var(--border));
    padding: 0.5rem 0;
    font-size: 0.83rem;
}
.operations dd {
    color: hsl(var(--muted-foreground));
}
@media (max-width: 750px) {
    .admin-grid {
        grid-template-columns: 1fr;
    }
    .dash-modules {
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 9rem), 1fr));
    }
}
</style>
