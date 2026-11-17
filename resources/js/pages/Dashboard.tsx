import AdminTablePreview from '@/components/AdminTablePreview';
import { useCurrencyPreference } from '@/composables/useCurrencyPreference';
import { adminTables, type AdminTableKey } from '@/mocks/admin';
import type { CartCurrency } from '@/types/commerce';
import '../../css/admin-preview.css';
import '../../css/dashboard.css';

import AppLayout from '@/layouts/AppLayout';
import { dashboard } from '@/mocks/store';
import type { SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    ArrowUpRight,
    Box,
    CircleDollarSign,
    ClipboardList,
    LayoutDashboard,
    Package,
    Settings,
    ShieldCheck,
    ShoppingBag,
    Truck,
    Users,
} from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';

const sections = ['Mi cuenta', 'Pedidos', 'Direcciones', 'Administración demo'] as const;
const modules = [
    { key: 'summary', label: 'Resumen', icon: LayoutDashboard },
    { key: 'products', label: adminTables.products.title, icon: Package },
    { key: 'catalog', label: adminTables.catalog.title, icon: Box },
    { key: 'inventory', label: adminTables.inventory.title, icon: ClipboardList },
    { key: 'suppliers', label: adminTables.suppliers.title, icon: Truck },
    { key: 'pricing', label: adminTables.pricing.title, icon: CircleDollarSign },
    { key: 'exchange', label: adminTables.exchange.title, icon: CircleDollarSign },
    { key: 'users', label: adminTables.users.title, icon: Users },
    { key: 'orders', label: adminTables.orders.title, icon: ShoppingBag },
    { key: 'sync', label: adminTables.sync.title, icon: Activity },
] as const satisfies readonly { key: 'summary' | AdminTableKey; label: string; icon: typeof LayoutDashboard }[];
const days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const metricIcons = [CircleDollarSign, ShoppingBag, Package, Activity, Box, CircleDollarSign];

export default function Dashboard() {
    const { user } = usePage<SharedData>().props.auth;
    const [section, setSection] = useState<(typeof sections)[number]>('Mi cuenta');
    const [activeModule, setActiveModule] = useState<(typeof modules)[number]>(modules[0]);
    const [currency, setCurrency] = useCurrencyPreference();
    const isAdminDemo = section === 'Administración demo';

    return (
        <AppLayout title="Mi cuenta">
            <Head title="Mi cuenta · Refugio Outdoor" />
            <main className="dash">
                <header className="dash-intro">
                    <div>
                        <p>REFUGIO OUTDOOR</p>
                        <h1>{isAdminDemo ? 'Panel de administración' : section}</h1>
                        <span>{isAdminDemo ? 'Datos ilustrativos · Vista de demostración' : `Hola, ${user?.name ?? ''}`}</span>
                    </div>
                    <Link href={route('home')}>
                        Volver a la tienda <ArrowUpRight size={17} />
                    </Link>
                </header>
                <nav className="dash-tabs" aria-label="Secciones de mi cuenta">
                    {sections.map((item) => (
                        <button key={item} type="button" aria-pressed={section === item} onClick={() => setSection(item)}>
                            {item}
                        </button>
                    ))}
                </nav>

                {section === 'Mi cuenta' && (
                    <div className="account-grid">
                        <section className="dash-card">
                            <Settings />
                            <h2>Perfil y seguridad</h2>
                            <p>Gestiona el perfil y el acceso de la cuenta Laravel.</p>
                            <p className="account-status">Correo: {user?.email_verified_at ? 'Verificado' : 'Sin verificación registrada'}.</p>
                            <Link href={route('profile.edit')}>
                                Editar perfil <ArrowUpRight size={16} />
                            </Link>
                            <Link href={route('password.edit')}>
                                Cambiar contraseña <ArrowUpRight size={16} />
                            </Link>
                        </section>
                        <section className="dash-card">
                            <ShoppingBag />
                            <h2>Pedidos y seguimiento</h2>
                            <p>Consulta una muestra del historial y los detalles de pedido.</p>
                            <button type="button" onClick={() => setSection('Pedidos')}>
                                Ver pedidos demo <ArrowUpRight size={16} />
                            </button>
                        </section>
                        <section className="dash-card">
                            <Truck />
                            <h2>Direcciones de entrega</h2>
                            <p>Vista previa de las direcciones que se podrán guardar en la cuenta.</p>
                            <button type="button" onClick={() => setSection('Direcciones')}>
                                Ver direcciones demo <ArrowUpRight size={16} />
                            </button>
                        </section>
                        <section className="dash-card">
                            <CircleDollarSign aria-hidden="true" />
                            <h2>Moneda de visualización</h2>
                            <p>Preferencia de este navegador. La moneda de cobro se confirmará en el servidor.</p>
                            <div className="account-preference">
                                <label htmlFor="account-currency">Moneda preferida</label>
                                <select id="account-currency" value={currency} onChange={(event) => setCurrency(event.target.value as CartCurrency)}>
                                    <option value="MXN">MXN · Peso mexicano</option>
                                    <option value="USD">USD · Dólar estadounidense</option>
                                </select>
                            </div>
                        </section>
                        <section className="dash-card">
                            <ShieldCheck aria-hidden="true" />
                            <h2>Métodos de acceso</h2>
                            <p>Correo y contraseña · Acceso de Laravel.</p>
                            <button type="button" disabled>
                                Google · Pendiente de integración
                            </button>
                        </section>
                    </div>
                )}

                {section === 'Pedidos' && (
                    <section className="dash-card">
                        <h2>Historial de ejemplo</h2>
                        <p>Pedidos ficticios para mostrar el diseño.</p>
                        {dashboard.orders.map((order) => (
                            <details key={order.id} className="account-order">
                                <summary>
                                    {order.id} · {order.status}
                                </summary>
                                <p>
                                    {order.date} · {order.total}
                                </p>
                                <dl className="operations">
                                    <div>
                                        <dt>Artículo de ejemplo</dt>
                                        <dd>{order.item}</dd>
                                    </div>
                                    <div>
                                        <dt>Seguimiento · Demo</dt>
                                        <dd>{order.tracking}</dd>
                                    </div>
                                </dl>
                            </details>
                        ))}
                    </section>
                )}

                {section === 'Direcciones' && (
                    <section className="dash-card">
                        <h2>Direcciones de ejemplo</h2>
                        <p>Cuando se integre el servicio, aquí podrás guardar y editar destinos.</p>
                        <p className="dash-empty">Todavía no hay direcciones guardadas en esta vista de demostración.</p>
                        <button type="button" disabled>
                            Añadir dirección · Pendiente
                        </button>
                    </section>
                )}

                {isAdminDemo && (
                    <div className="admin-grid">
                        <nav className="dash-modules dash-card" aria-label="Módulos administrativos de demostración">
                            {modules.map((item) => (
                                <button key={item.label} type="button" aria-pressed={activeModule === item} onClick={() => setActiveModule(item)}>
                                    <item.icon size={18} /> {item.label}
                                </button>
                            ))}
                        </nav>
                        {activeModule.key === 'summary' ? <AdminSummary /> : <AdminTablePreview key={activeModule.key} dataset={activeModule.key} />}
                    </div>
                )}
            </main>
        </AppLayout>
    );
}

function AdminSummary() {
    return (
        <div className="admin-content">
            <div className="metrics-grid">
                {dashboard.metrics.map((metric, index) => {
                    const Icon = metricIcons[index];
                    return (
                        <article key={metric.label} className="dash-card metric">
                            <div>
                                <p>{metric.label}</p>
                                <Icon size={18} />
                            </div>
                            <strong>{metric.value}</strong>
                            <small>{metric.note}</small>
                        </article>
                    );
                })}
            </div>
            <div className="account-grid admin-panels">
                <section className="dash-card">
                    <h2>Ventas por día</h2>
                    <p>Últimos 7 días · MXN · Demo</p>
                    <div className="sales-chart" aria-hidden="true">
                        {dashboard.sales.map((value, index) => (
                            <div key={days[index]}>
                                <span style={{ height: `${value / 280}%` }}></span>
                                <small>{days[index]}</small>
                            </div>
                        ))}
                    </div>
                    <details>
                        <summary>Ver datos de la gráfica</summary>
                        <ul>
                            {dashboard.sales.map((value, index) => (
                                <li key={days[index]}>
                                    {days[index]} · MXN {value.toLocaleString('es-MX')}
                                </li>
                            ))}
                        </ul>
                    </details>
                </section>
                <section className="dash-card">
                    <h2>Los 3 productos más vendidos</h2>
                    <p>Unidades vendidas · Demostración</p>
                    <ol className="operations">
                        {dashboard.topProducts.map(({ name, units }) => (
                            <li key={name}>
                                {name} · {units} unidades
                            </li>
                        ))}
                    </ol>
                </section>
                <section className="dash-card">
                    <h2>Estado operativo</h2>
                    <dl className="operations">
                        <div>
                            <dt>Redis</dt>
                            <dd>Pendiente de conexión</dd>
                        </div>
                        <div>
                            <dt>Elasticsearch</dt>
                            <dd>Pendiente de conexión</dd>
                        </div>
                        <div>
                            <dt>Banxico · Tipo de cambio</dt>
                            <dd>Pendiente de consulta · FIX de ejemplo en Banxico</dd>
                        </div>
                        <div>
                            <dt>Pagos · Stripe</dt>
                            <dd>Pendiente de conexión</dd>
                        </div>
                        <div>
                            <dt>Alertas operativas</dt>
                            <dd>Sin fuente conectada</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    );
}
