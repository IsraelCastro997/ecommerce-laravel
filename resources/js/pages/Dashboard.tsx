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
    ShoppingBag,
    Truck,
    Users,
} from 'lucide-react';
import { useState } from 'react';
import { route } from 'ziggy-js';

const sections = ['Mi cuenta', 'Pedidos', 'Direcciones', 'Administración demo'] as const;
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
] as const;
const days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const metricIcons = [CircleDollarSign, ShoppingBag, Package, Activity];
type PreviewState = 'empty' | 'loading' | 'error';

export default function Dashboard() {
    const { user } = usePage<SharedData>().props.auth;
    const [section, setSection] = useState<(typeof sections)[number]>('Mi cuenta');
    const [activeModule, setActiveModule] = useState<(typeof modules)[number]>(modules[0]);
    const [previewState, setPreviewState] = useState<PreviewState>('empty');
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
                            <p>Actualiza tu nombre o correo y administra tu contraseña.</p>
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
                    </div>
                )}

                {section === 'Pedidos' && (
                    <section className="dash-card">
                        <h2>Historial de ejemplo</h2>
                        <p>Pedidos ficticios para mostrar el diseño.</p>
                        {dashboard.orders.map((order) => (
                            <details key={order.id}>
                                <summary>
                                    {order.id} · {order.status}
                                </summary>
                                <p>
                                    {order.date} · {order.total}
                                </p>
                                <p>Seguimiento pendiente de integración.</p>
                            </details>
                        ))}
                    </section>
                )}

                {section === 'Direcciones' && (
                    <section className="dash-card">
                        <h2>Direcciones de ejemplo</h2>
                        <p>Cuando se integre el servicio, aquí podrás guardar y editar destinos.</p>
                        <p className="dash-empty">Todavía no hay direcciones guardadas en esta vista de demostración.</p>
                    </section>
                )}

                {isAdminDemo && (
                    <div className="admin-grid">
                        <nav className="dash-modules dash-card" aria-label="Módulos administrativos de demostración">
                            {modules.map((item) => (
                                <button
                                    key={item.label}
                                    type="button"
                                    aria-pressed={activeModule === item}
                                    onClick={() => {
                                        setActiveModule(item);
                                        setPreviewState('empty');
                                    }}
                                >
                                    <item.icon size={18} /> {item.label}
                                </button>
                            ))}
                        </nav>
                        {activeModule.label === 'Resumen' ? (
                            <AdminSummary />
                        ) : (
                            <section className="dash-card">
                                <h2>{activeModule.label}</h2>
                                <p>{activeModule.detail}</p>
                                <label className="state-select">
                                    Estado de demostración
                                    <select value={previewState} onChange={(event) => setPreviewState(event.target.value as PreviewState)}>
                                        <option value="empty">Vacío</option>
                                        <option value="loading">Cargando</option>
                                        <option value="error">Error de carga</option>
                                    </select>
                                </label>
                                {previewState === 'loading' && (
                                    <div className="dash-empty" role="status">
                                        <p>Cargando datos de ejemplo…</p>
                                        <div className="admin-skeleton" aria-hidden="true">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                    </div>
                                )}
                                {previewState === 'error' && (
                                    <p className="dash-empty" role="alert">
                                        No se pudo cargar esta vista de ejemplo.{' '}
                                        <button type="button" onClick={() => setPreviewState('empty')}>
                                            Volver al estado vacío
                                        </button>
                                    </p>
                                )}
                                {previewState === 'empty' && (
                                    <p className="dash-empty" role="status">
                                        Sin registros en esta demostración. Tablas, filtros y paginación de Laravel pendientes de integración.
                                    </p>
                                )}
                            </section>
                        )}
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
    );
}
