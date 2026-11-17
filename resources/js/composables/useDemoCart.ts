import { products, type Product } from '@/mocks/store';
import { useEffect, useState } from 'react';

const key = 'refugio:demo-cart:v1';
export const DEMO_CART_LIMIT = 9;

function readCart(): Record<string, number> {
    try {
        const raw: unknown = JSON.parse(window.localStorage.getItem(key) ?? '{}');
        if (!raw || typeof raw !== 'object' || Array.isArray(raw)) return {};
        return Object.fromEntries(
            products
                .filter((product) => product.available)
                .flatMap(({ id }) => {
                    const quantity = (raw as Record<string, unknown>)[id];
                    return typeof quantity === 'number' && Number.isInteger(quantity) && quantity > 0
                        ? [[id, Math.min(quantity, DEMO_CART_LIMIT)]]
                        : [];
                }),
        );
    } catch {
        return {};
    }
}

export function useDemoCart() {
    const [quantities, setQuantities] = useState(readCart);

    useEffect(() => {
        try {
            window.localStorage.setItem(key, JSON.stringify(quantities));
        } catch {
            // The demo still works in memory when browser storage is unavailable.
        }
    }, [quantities]);

    function changeQuantity(product: Product, delta: 1 | -1 | 'remove') {
        if (!product.available) return;
        setQuantities((current) => {
            const next = { ...current };
            const quantity = delta === 'remove' ? 0 : Math.min(DEMO_CART_LIMIT, (current[product.id] ?? 0) + delta);
            if (quantity > 0) next[product.id] = quantity;
            else delete next[product.id];
            return next;
        });
    }

    return {
        quantities,
        items: products.filter((product) => quantities[product.id]),
        itemCount: Object.values(quantities).reduce((count, quantity) => count + quantity, 0),
        changeQuantity,
        empty: () => setQuantities({}),
    };
}
