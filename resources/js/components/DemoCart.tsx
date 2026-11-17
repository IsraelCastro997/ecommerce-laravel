import { DEMO_CART_LIMIT, useDemoCart } from '@/composables/useDemoCart';
import { formatPrice } from '@/mocks/store';
import type { CartCurrency } from '@/types/commerce';
import { ArrowRight } from 'lucide-react';
import { useState } from 'react';

type AvailabilityPreview = 'ready' | 'loading' | 'unavailable';

export default function DemoCart({
    cart,
    currency,
    onCheckout,
}: {
    cart: ReturnType<typeof useDemoCart>;
    currency: CartCurrency;
    onCheckout: () => void;
}) {
    const [preview, setPreview] = useState<AvailabilityPreview>('ready');
    const firstItem = cart.items[0];

    return (
        <>
            <h2 id="panel-title">Tu carrito</h2>
            <p>Carrito demo guardado en este navegador.</p>
            <p className="sr-only" role="status">
                {cart.itemCount} artículos en el carrito demo
            </p>
            {firstItem ? (
                <>
                    <ul className="cart-list">
                        {cart.items.map((product) => (
                            <li key={product.id}>
                                <strong>{product.name}</strong>
                                <span>{formatPrice(product.prices[currency], currency)} por unidad · estimado</span>
                                <div className="cart-controls">
                                    <button
                                        aria-label={`Quitar una unidad de ${product.name}`}
                                        type="button"
                                        onClick={() => cart.changeQuantity(product, -1)}
                                    >
                                        −
                                    </button>
                                    <output aria-label={`Cantidad de ${product.name}`}>{cart.quantities[product.id]}</output>
                                    <button
                                        aria-label={`Agregar una unidad de ${product.name}`}
                                        disabled={cart.quantities[product.id] >= DEMO_CART_LIMIT}
                                        type="button"
                                        onClick={() => cart.changeQuantity(product, 1)}
                                    >
                                        +
                                    </button>
                                    <button
                                        className="text-button"
                                        type="button"
                                        aria-label={`Eliminar ${product.name}`}
                                        onClick={() => cart.changeQuantity(product, 'remove')}
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </li>
                        ))}
                    </ul>
                    <button
                        className="text-button"
                        type="button"
                        onClick={() => {
                            cart.empty();
                            setPreview('ready');
                        }}
                    >
                        Vaciar carrito de demostración
                    </button>
                    <div className="cart-preview">
                        <label htmlFor="cart-preview-state">Estado de disponibilidad (demo)</label>
                        <select id="cart-preview-state" value={preview} onChange={(event) => setPreview(event.target.value as AvailabilityPreview)}>
                            <option value="ready">Disponible</option>
                            <option value="loading">Cargando</option>
                            <option value="unavailable">Error de disponibilidad</option>
                        </select>
                    </div>
                    {preview === 'loading' && (
                        <p className="store-empty" role="status">
                            Comprobación de disponibilidad en curso · Simulación
                        </p>
                    )}
                    {preview === 'unavailable' && (
                        <p className="store-empty" role="alert">
                            Simulación: no hay disponibilidad suficiente para {firstItem.name}.
                        </p>
                    )}
                    <p>Subtotal, envío e impuestos pendientes de confirmación.</p>
                    <button className="store-button primary" type="button" disabled={preview !== 'ready'} onClick={onCheckout}>
                        Ver checkout demo <ArrowRight size={18} />
                    </button>
                </>
            ) : (
                <p className="store-empty">Tu carrito de demostración está vacío.</p>
            )}
        </>
    );
}
