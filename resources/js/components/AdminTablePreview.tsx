import { adminTables, type AdminTableKey, type PreviewDataset } from '@/mocks/admin';
import { categories, products } from '@/mocks/store';
import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import { useLayoutEffect, useRef, useState } from 'react';

type PreviewState = 'rows' | 'loading' | 'empty' | 'error';

function normalize(value: string) {
    return value
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLocaleLowerCase('es-MX');
}

export default function AdminTablePreview({ dataset }: { dataset: AdminTableKey }) {
    const { title, description, columns, rows }: PreviewDataset = adminTables[dataset];
    const [query, setQuery] = useState('');
    const [status, setStatus] = useState('');
    const [from, setFrom] = useState('');
    const [to, setTo] = useState('');
    const [view, setView] = useState<PreviewState>('rows');
    const [selection, setSelection] = useState<{ id?: string } | null>(null);
    const dialog = useRef<HTMLDialogElement>(null);
    const statuses = [...new Set(rows.map((row) => row.status))];
    const matches = rows.filter(
        (row) =>
            (!status || row.status === status) &&
            (!from || (row.occurredOn && row.occurredOn >= from)) &&
            (!to || (row.occurredOn && row.occurredOn <= to)) &&
            normalize([...row.cells, row.status].join(' ')).includes(normalize(query.trim())),
    );
    const selectedRow = rows.find((row) => row.id === selection?.id);
    const selectedProduct = dataset === 'products' ? products.find((product) => product.id === selection?.id) : undefined;
    const productForm = dataset === 'products';
    const dialogTitle = productForm ? (selection?.id ? 'Editar producto demo' : 'Crear producto demo') : 'Detalle de demostración';

    useLayoutEffect(() => {
        if (selection && !dialog.current?.open) dialog.current?.showModal();
    }, [selection]);

    return (
        <section className="dash-card admin-preview" aria-labelledby="admin-preview-title">
            <header className="admin-preview-heading">
                <div>
                    <h2 id="admin-preview-title">{title}</h2>
                    <p>{description}</p>
                </div>
                {productForm && (
                    <button className="admin-preview-action" type="button" onClick={() => setSelection({})}>
                        Nuevo producto demo
                    </button>
                )}
            </header>
            <div className="admin-filters">
                <label>
                    Buscar en {title.toLocaleLowerCase('es-MX')}
                    <input type="search" placeholder="Buscar ejemplos" value={query} onChange={(event) => setQuery(event.target.value)} />
                </label>
                <div className="admin-control">
                    <label htmlFor="admin-filter-status">Filtrar por estado</label>
                    <select id="admin-filter-status" value={status} onChange={(event) => setStatus(event.target.value)}>
                        <option value="">Todos los estados</option>
                        {statuses.map((value) => (
                            <option key={value}>{value}</option>
                        ))}
                    </select>
                </div>
                {dataset === 'orders' && (
                    <>
                        <label>
                            Fecha desde
                            <input type="date" max={to || undefined} value={from} onChange={(event) => setFrom(event.target.value)} />
                        </label>
                        <label>
                            Fecha hasta
                            <input type="date" min={from || undefined} value={to} onChange={(event) => setTo(event.target.value)} />
                        </label>
                    </>
                )}
                <div className="admin-control">
                    <label htmlFor="admin-preview-state">Estado de demostración</label>
                    <select id="admin-preview-state" value={view} onChange={(event) => setView(event.target.value as PreviewState)}>
                        <option value="rows">Con registros</option>
                        <option value="loading">Cargando</option>
                        <option value="empty">Vacío</option>
                        <option value="error">Error de carga</option>
                    </select>
                </div>
            </div>
            {view === 'loading' && (
                <div className="dash-empty" role="status">
                    <p>Cargando datos de ejemplo…</p>
                    <div className="admin-skeleton" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            )}
            {view === 'error' && (
                <div className="dash-empty" role="alert">
                    <p>No se pudo cargar esta vista de ejemplo.</p>
                    <button type="button" onClick={() => setView('rows')}>
                        Restablecer la demostración
                    </button>
                </div>
            )}
            {(view === 'empty' || (view === 'rows' && !matches.length)) && (
                <p className="dash-empty" role="status">
                    Sin registros de demostración para los filtros actuales.
                </p>
            )}
            {view === 'rows' && matches.length > 0 && (
                <>
                    <div className="admin-table-wrap" tabIndex={0} role="region" aria-label={`Tabla de ${title} con desplazamiento horizontal`}>
                        <table className="admin-table">
                            <caption>Registros de demostración · {title}</caption>
                            <thead>
                                <tr>
                                    {[...columns, 'Estado', 'Acciones'].map((column) => (
                                        <th key={column} scope="col">
                                            {column}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {matches.map((row) => (
                                    <tr key={row.id}>
                                        {row.cells.map((cell, index) =>
                                            index === 0 ? (
                                                <th key={columns[index]} scope="row">
                                                    {cell}
                                                </th>
                                            ) : (
                                                <td key={columns[index]}>{cell}</td>
                                            ),
                                        )}
                                        <td>
                                            <span className="admin-status">{row.status}</span>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                className="admin-row-action"
                                                aria-label={`${productForm ? 'Editar' : 'Ver detalle de'} ${row.cells.slice(0, 2).join(' ')}`}
                                                onClick={() => setSelection({ id: row.id })}
                                            >
                                                {productForm ? 'Editar demo' : 'Ver detalle'}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="admin-page-controls">
                        <p role="status">
                            {matches.length} de {rows.length} ejemplos · Página única de muestra
                        </p>
                        <nav aria-label="Paginación de demostración">
                            <button type="button" disabled>
                                <ChevronLeft size={15} /> Anterior
                            </button>
                            <button type="button" disabled>
                                Siguiente <ChevronRight size={15} />
                            </button>
                        </nav>
                    </div>
                </>
            )}
            <dialog className="admin-dialog" ref={dialog} aria-labelledby="admin-dialog-title" onClose={() => setSelection(null)}>
                {selection && (
                    <>
                        <div className="admin-preview-heading">
                            <h2 id="admin-dialog-title">{dialogTitle}</h2>
                            <button type="button" aria-label="Cerrar vista previa" onClick={() => dialog.current?.close()}>
                                <X size={20} />
                            </button>
                        </div>
                        <p>Demostración sin cambios en el catálogo ni acciones de servidor.</p>
                        {productForm ? (
                            <>
                                <div className="admin-filters admin-form">
                                    <label>
                                        Nombre
                                        <input defaultValue={selectedProduct?.name} />
                                    </label>
                                    <label>
                                        SKU
                                        <input defaultValue={selectedProduct?.id} />
                                    </label>
                                    <label>
                                        Marca
                                        <input defaultValue={selectedProduct?.brand} />
                                    </label>
                                    <label>
                                        Categoría
                                        <select defaultValue={selectedProduct?.category ?? ''}>
                                            <option value="">Seleccionar categoría</option>
                                            {categories.map((item) => (
                                                <option key={item}>{item}</option>
                                            ))}
                                        </select>
                                    </label>
                                    <label>
                                        Publicación
                                        <select defaultValue={selectedRow?.status ?? 'Borrador'}>
                                            <option>Borrador</option>
                                            <option>Publicado</option>
                                        </select>
                                    </label>
                                    <label>
                                        Precio USD (demo)
                                        <input inputMode="decimal" defaultValue={selectedProduct?.prices.USD} />
                                    </label>
                                    <label>
                                        Precio MXN (demo)
                                        <input inputMode="decimal" defaultValue={selectedProduct?.prices.MXN} />
                                    </label>
                                    <label>
                                        Stock (demo)
                                        <input type="number" min="0" />
                                    </label>
                                    <label>
                                        Fecha de vigencia
                                        <input type="date" />
                                    </label>
                                    <label>
                                        Descripción corta
                                        <input defaultValue={selectedProduct?.name} />
                                    </label>
                                    <label>
                                        Descripción larga
                                        <textarea defaultValue={selectedProduct?.detail} />
                                    </label>
                                    <label>
                                        Imagen de catálogo
                                        <input type="file" accept="image/*" disabled />
                                    </label>
                                    <label>
                                        Atributos
                                        <textarea defaultValue={selectedProduct?.detail} />
                                    </label>
                                </div>
                                <button className="admin-preview-action" type="button" disabled>
                                    Guardar cambios
                                </button>
                            </>
                        ) : (
                            selectedRow && (
                                <dl className="operations">
                                    {columns.map((column, index) => (
                                        <div key={column}>
                                            <dt>{column}</dt>
                                            <dd>{selectedRow.cells[index]}</dd>
                                        </div>
                                    ))}
                                    <div>
                                        <dt>Estado</dt>
                                        <dd>{selectedRow.status}</dd>
                                    </div>
                                </dl>
                            )
                        )}
                    </>
                )}
            </dialog>
        </section>
    );
}
