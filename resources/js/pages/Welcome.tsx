import '../../css/store.css';

import { categories, formatPrice, products, type Currency, type Product } from '@/mocks/store';
import type { SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowLeft,
    ArrowRight,
    Backpack,
    Compass,
    Droplet,
    Grid2X2,
    List,
    Mountain,
    PackageCheck,
    Search,
    ShieldCheck,
    ShoppingBag,
    Tent,
    Truck,
    UserRound,
    X,
    Zap,
} from 'lucide-react';
import { useLayoutEffect, useRef, useState } from 'react';
import { route } from 'ziggy-js';

type Panel = { type: 'product'; product: Product } | { type: 'cart' | 'checkout' } | { type: 'help'; topic: string };

const categoryIcons = [Backpack, Tent, Droplet, Zap];
const topics = ['Contacto', 'Preguntas frecuentes', 'Envíos y devoluciones', 'Términos', 'Privacidad'];

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;
    const [currency, setCurrency] = useState<Currency>('MXN');
    const [query, setQuery] = useState('');
    const [category, setCategory] = useState('');
    const [brand, setBrand] = useState('');
    const [stock, setStock] = useState(false);
    const [maximum, setMaximum] = useState('');
    const [sort, setSort] = useState('');
    const [list, setList] = useState(false);
    const [panel, setPanel] = useState<Panel | null>(null);
    const [bag, setBag] = useState<Record<string, number>>({});
    const searchInput = useRef<HTMLInputElement>(null);
    const dialog = useRef<HTMLDialogElement>(null);
    const closeButton = useRef<HTMLButtonElement>(null);
    const items = products.filter((product) => bag[product.id]);
    const itemCount = Object.values(bag).reduce((count, quantity) => count + quantity, 0);

    const phrase = query.trim().toLocaleLowerCase('es-MX');
    const visible = products.filter(
        (product) =>
            (!phrase ||
                [product.name, product.category, product.brand, product.id].some((value) => value.toLocaleLowerCase('es-MX').includes(phrase))) &&
            (!category || product.category === category) &&
            (!brand || product.brand === brand) &&
            (!stock || product.available) &&
            (maximum === '' || product.prices[currency] <= Number(maximum)),
    );
    if (sort === 'ascending') visible.sort((a, b) => a.prices[currency] - b.prices[currency]);
    if (sort === 'descending') visible.sort((a, b) => b.prices[currency] - a.prices[currency]);

    useLayoutEffect(() => {
        if (panel && dialog.current) {
            if (!dialog.current.open) dialog.current.showModal();
            closeButton.current?.focus();
        }
    }, [panel]);

    function resetFilters(nextCategory = '') {
        setQuery('');
        setCategory(nextCategory);
        setBrand('');
        setStock(false);
        setMaximum('');
        setSort('');
    }

    function changeQuantity(product: Product, delta: number | 'remove') {
        setBag((previous) => {
            const next = { ...previous };
            const quantity = delta === 'remove' ? 0 : Math.min(9, (previous[product.id] || 0) + delta);
            if (quantity > 0) next[product.id] = quantity;
            else delete next[product.id];
            return next;
        });
    }

    return (
        <div className="refugio">
            <Head title="Refugio Outdoor · Equipo para explorar" />
            <a className="skip-navigation" href="#contenido">
                Saltar al contenido
            </a>
            <div className="store-announcement">
                <Compass size={15} /> Tienda de demostración · Precios y disponibilidad ilustrativos
            </div>
            <header className="store-header">
                <div className="store-wrap header-inner">
                    <Link className="store-mark" href="/" aria-label="Refugio Outdoor, inicio">
                        <Mountain size={29} />{' '}
                        <span>
                            REFUGIO<small>OUTDOOR</small>
                        </span>
                    </Link>
                    <nav className="main-links" aria-label="Navegación principal">
                        <a href="#catalogo">Catálogo</a>
                        <a href="#categorias">Categorías</a>
                        <a href="#ayuda">Ayuda</a>
                    </nav>
                    <div className="store-actions">
                        <select
                            value={currency}
                            aria-label="Moneda"
                            onChange={(event) => {
                                setCurrency(event.target.value as Currency);
                                setMaximum('');
                            }}
                        >
                            <option>MXN</option>
                            <option>USD</option>
                        </select>
                        <button className="icon-button" type="button" aria-label="Buscar en el catálogo" onClick={() => searchInput.current?.focus()}>
                            <Search size={20} />
                        </button>
                        <Link
                            className="icon-button"
                            href={route(auth.user ? 'dashboard' : 'login')}
                            aria-label={auth.user ? 'Mi cuenta' : 'Iniciar sesión'}
                        >
                            <UserRound size={20} />
                        </Link>
                        <button
                            className="icon-button bag-button"
                            type="button"
                            aria-label={`Abrir carrito, ${itemCount} artículos`}
                            onClick={() => setPanel({ type: 'cart' })}
                        >
                            <ShoppingBag size={20} />
                            <span aria-hidden="true">{itemCount}</span>
                        </button>
                    </div>
                </div>
            </header>

            <main id="contenido" className="store-wrap" tabIndex={-1}>
                <section className="store-hero">
                    <div className="hero-copy">
                        <p className="eyebrow">
                            <span></span> EQUIPO PARA EXPLORAR
                        </p>
                        <h1>
                            El camino
                            <br />
                            empieza afuera.
                        </h1>
                        <p>Prepara tu próxima salida con equipo para el sendero, el campamento y todo lo que hay en medio.</p>
                        <a className="store-button primary" href="#catalogo">
                            Explorar catálogo <ArrowRight size={18} />
                        </a>
                    </div>
                    <a className="hero-down" href="#categorias" aria-label="Ir a categorías">
                        <ArrowDown size={19} />
                    </a>
                </section>

                <section id="categorias" className="store-section" aria-labelledby="category-title">
                    <div className="section-heading">
                        <div>
                            <p className="eyebrow">ENCUENTRA TU EQUIPO</p>
                            <h2 id="category-title">Explora por categoría</h2>
                        </div>
                    </div>
                    <div className="category-grid">
                        {categories.map((group, index) => {
                            const Icon = categoryIcons[index];
                            return (
                                <a key={group} href="#catalogo" onClick={() => resetFilters(group)}>
                                    <span className="category-icon">
                                        <Icon size={24} />
                                    </span>
                                    <strong>{group}</strong>
                                    <ArrowRight size={17} className="category-arrow" />
                                </a>
                            );
                        })}
                    </div>
                </section>

                <section id="catalogo" className="store-section" aria-labelledby="catalog-title">
                    <div className="section-heading">
                        <div>
                            <p className="eyebrow">CATÁLOGO DEMO</p>
                            <h2 id="catalog-title">Esenciales para salir</h2>
                        </div>
                        <div className="view-controls" aria-label="Vista del catálogo">
                            <button
                                className="icon-button"
                                type="button"
                                aria-label="Vista en cuadrícula"
                                aria-pressed={!list}
                                onClick={() => setList(false)}
                            >
                                <Grid2X2 size={19} />
                            </button>
                            <button
                                className="icon-button"
                                type="button"
                                aria-label="Vista en lista"
                                aria-pressed={list}
                                onClick={() => setList(true)}
                            >
                                <List size={21} />
                            </button>
                        </div>
                    </div>
                    <div className="catalog-layout">
                        <aside className="catalog-filters" aria-label="Filtros del catálogo">
                            <label>
                                Buscar{' '}
                                <input
                                    ref={searchInput}
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    type="search"
                                    list="product-suggestions"
                                    placeholder="Producto, marca o SKU"
                                />
                            </label>
                            <datalist id="product-suggestions">
                                {products.map((product) => (
                                    <option key={product.id} value={product.name} />
                                ))}
                            </datalist>
                            <label>
                                Categoría{' '}
                                <select value={category} onChange={(event) => setCategory(event.target.value)}>
                                    <option value="">Todas</option>
                                    {categories.map((group) => (
                                        <option key={group}>{group}</option>
                                    ))}
                                </select>
                            </label>
                            <label>
                                Marca{' '}
                                <select value={brand} onChange={(event) => setBrand(event.target.value)}>
                                    <option value="">Todas</option>
                                    <option>Refugio</option>
                                    <option>Cumbre</option>
                                </select>
                            </label>
                            <label>
                                Precio máximo ({currency}){' '}
                                <input
                                    value={maximum}
                                    onChange={(event) => setMaximum(event.target.value)}
                                    type="number"
                                    min="0"
                                    placeholder="Sin límite"
                                />
                            </label>
                            <label className="stock-filter">
                                <input checked={stock} onChange={(event) => setStock(event.target.checked)} type="checkbox" /> Solo disponibles
                            </label>
                            <button className="text-button" type="button" onClick={() => resetFilters()}>
                                Limpiar filtros
                            </button>
                        </aside>
                        <div>
                            <div className="results-bar">
                                <p role="status">
                                    {visible.length} {visible.length === 1 ? 'producto' : 'productos'} de ejemplo
                                </p>
                                <select value={sort} onChange={(event) => setSort(event.target.value)} aria-label="Ordenar productos">
                                    <option value="">Destacados</option>
                                    <option value="ascending">Menor precio</option>
                                    <option value="descending">Mayor precio</option>
                                </select>
                            </div>
                            {visible.length ? (
                                <div className={list ? 'product-grid product-list' : 'product-grid'}>
                                    {visible.map((product) => (
                                        <article key={product.id} className="product-card">
                                            <div
                                                className="product-photo"
                                                role="img"
                                                aria-label={product.name}
                                                style={{ backgroundPosition: product.image }}
                                            ></div>
                                            <div className="product-copy">
                                                <p>
                                                    {product.brand} · {product.category}
                                                </p>
                                                <h3>
                                                    <button type="button" onClick={() => setPanel({ type: 'product', product })}>
                                                        {product.name}
                                                    </button>
                                                </h3>
                                                <strong>{formatPrice(product.prices[currency], currency)}</strong>
                                                <small>Precio estimado</small>
                                            </div>
                                        </article>
                                    ))}
                                </div>
                            ) : (
                                <p className="store-empty">
                                    No encontramos productos de ejemplo con esos filtros.{' '}
                                    <button type="button" className="text-button" onClick={() => resetFilters()}>
                                        Ver todo
                                    </button>
                                </p>
                            )}
                            <p className="page-note">Página de muestra · el catálogo real usará paginación de Laravel.</p>
                        </div>
                    </div>
                </section>

                <section className="store-benefits" aria-label="Información de compra">
                    <div>
                        <Truck />
                        <strong>Envíos</strong>
                        <span>Opciones por confirmar en checkout</span>
                    </div>
                    <div>
                        <ShieldCheck />
                        <strong>Pagos</strong>
                        <span>Integración en preparación</span>
                    </div>
                    <div>
                        <PackageCheck />
                        <strong>Devoluciones</strong>
                        <span>Condiciones pendientes de publicar</span>
                    </div>
                </section>
            </main>
            <footer id="ayuda" className="store-footer">
                <div className="store-wrap">
                    <div className="store-mark">
                        <Mountain size={28} />{' '}
                        <span>
                            REFUGIO<small>OUTDOOR</small>
                        </span>
                    </div>
                    <nav aria-label="Información de la tienda">
                        {topics.map((topic) => (
                            <button key={topic} type="button" onClick={() => setPanel({ type: 'help', topic })}>
                                {topic}
                            </button>
                        ))}
                    </nav>
                </div>
            </footer>

            <dialog ref={dialog} className="store-dialog" aria-labelledby="panel-title" onClose={() => setPanel(null)}>
                <button
                    ref={closeButton}
                    className="icon-button close-panel"
                    type="button"
                    aria-label="Cerrar"
                    onClick={() => dialog.current?.close()}
                >
                    <X size={22} />
                </button>
                <p className="eyebrow">DEMOSTRACIÓN</p>
                {panel?.type === 'product' &&
                    (() => {
                        const { product } = panel;
                        return (
                            <>
                                <h2 id="panel-title">{product.name}</h2>
                                <div
                                    className="product-photo"
                                    role="img"
                                    aria-label={product.name}
                                    style={{ backgroundPosition: product.image }}
                                ></div>
                                <p>{product.detail}</p>
                                <p>
                                    {product.id} · {product.brand} · Origen: {product.origin}
                                </p>
                                <p>
                                    <strong>{formatPrice(product.prices[currency], currency)}</strong> · Precio estimado
                                </p>
                                <p>{product.available ? 'Disponible en la muestra' : 'Agotado en la muestra'}</p>
                                <button
                                    className="store-button primary"
                                    type="button"
                                    disabled={!product.available}
                                    onClick={() => {
                                        changeQuantity(product, 1);
                                        setPanel({ type: 'cart' });
                                    }}
                                >
                                    Agregar al carrito demo <ShoppingBag size={18} />
                                </button>
                                <h3 className="related-heading">También en el catálogo</h3>
                                <ul>
                                    {products
                                        .filter((item) => item.id !== product.id)
                                        .slice(0, 2)
                                        .map((item) => (
                                            <li key={item.id}>
                                                <button
                                                    className="text-button"
                                                    type="button"
                                                    onClick={() => setPanel({ type: 'product', product: item })}
                                                >
                                                    {item.name}
                                                </button>
                                            </li>
                                        ))}
                                </ul>
                            </>
                        );
                    })()}
                {panel?.type === 'cart' && (
                    <>
                        <h2 id="panel-title">Tu carrito</h2>
                        {!items.length ? (
                            <p className="store-empty">Tu carrito de demostración está vacío.</p>
                        ) : (
                            <ul className="cart-list">
                                {items.map((product) => (
                                    <li key={product.id}>
                                        <strong>{product.name}</strong>
                                        <span>{formatPrice(product.prices[currency], currency)} por unidad · estimado</span>
                                        <div className="cart-controls">
                                            <button
                                                aria-label={`Quitar una unidad de ${product.name}`}
                                                type="button"
                                                onClick={() => changeQuantity(product, -1)}
                                            >
                                                −
                                            </button>
                                            <output aria-label={`Cantidad de ${product.name}`}>{bag[product.id]}</output>
                                            <button
                                                aria-label={`Agregar una unidad de ${product.name}`}
                                                disabled={bag[product.id] >= 9}
                                                type="button"
                                                onClick={() => changeQuantity(product, 1)}
                                            >
                                                +
                                            </button>
                                            <button className="text-button" type="button" onClick={() => changeQuantity(product, 'remove')}>
                                                Eliminar
                                            </button>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <p>Subtotal, envío e impuestos pendientes de confirmación.</p>
                        {items.length > 0 && (
                            <button className="store-button primary" type="button" onClick={() => setPanel({ type: 'checkout' })}>
                                Ver checkout demo <ArrowRight size={18} />
                            </button>
                        )}
                    </>
                )}
                {panel?.type === 'checkout' && (
                    <>
                        <h2 id="panel-title">Confirmar pedido</h2>
                        <p>
                            Vista previa de {itemCount} artículos en {currency}. Introduce solo datos ficticios para explorar el formulario.
                        </p>
                        <div className="checkout-fields">
                            <label>
                                Dirección de entrega <input placeholder="Calle y número" />
                            </label>
                            <label>
                                Código postal <input inputMode="numeric" placeholder="00000" />
                            </label>
                            <label>
                                Envío{' '}
                                <select>
                                    <option>Consultar opciones al integrar</option>
                                </select>
                            </label>
                            <label>
                                Cupón <input placeholder="Código de muestra" />
                            </label>
                        </div>
                        <p>Resumen pendiente de validación del servidor.</p>
                        <div className="cart-controls">
                            <button className="text-button" type="button" onClick={() => setPanel({ type: 'cart' })}>
                                <ArrowLeft size={17} /> Volver al carrito
                            </button>
                            <button className="store-button primary" type="button" disabled>
                                Continuar al pago
                            </button>
                        </div>
                    </>
                )}
                {panel?.type === 'help' && (
                    <>
                        <h2 id="panel-title">{panel.topic}</h2>
                        {panel.topic === 'Preguntas frecuentes' ? (
                            <p>
                                Puedes explorar el catálogo, cambiar la moneda de los precios de muestra y probar el carrito. Las compras estarán
                                disponibles cuando se integre el servicio de pedidos.
                            </p>
                        ) : (
                            <p>La información de {panel.topic.toLocaleLowerCase('es-MX')} se publicará antes de habilitar pedidos.</p>
                        )}
                    </>
                )}
            </dialog>
        </div>
    );
}
