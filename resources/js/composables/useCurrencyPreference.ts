import type { CartCurrency } from '@/types/commerce';
import { useState } from 'react';

const key = 'refugio:display-currency';

function readCurrency(): CartCurrency {
    try {
        return window.localStorage.getItem(key) === 'USD' ? 'USD' : 'MXN';
    } catch {
        return 'MXN';
    }
}

export function useCurrencyPreference() {
    const [currency, setCurrency] = useState(readCurrency);

    function saveCurrency(value: CartCurrency) {
        setCurrency(value);
        try {
            window.localStorage.setItem(key, value);
        } catch {
            // The preference works in memory when browser storage is unavailable.
        }
    }

    return [currency, saveCurrency] as const;
}
