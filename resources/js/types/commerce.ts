export type CartCurrency = 'MXN' | 'USD';

// Proposed transport types. The existing storefront uses distinct demo fixtures.
export type Money = { amount: string; currency: CartCurrency };

export interface CartItem {
    id: string;
    productId: string;
    sku: string;
    name: string;
    requestedQuantity: number;
    authorizedQuantity: number | null;
    basePrice: Money;
    displayUnitPrice: Money;
    confirmedUnitPrice: Money | null;
}

export interface CartTotals {
    subtotal: Money;
    discount: Money;
    tax: Money;
    shipping: Money | null;
    total: Money | null;
}

export interface Cart {
    revision: string;
    currency: CartCurrency;
    items: CartItem[];
    totals: CartTotals | null;
}

export interface CartValidationError {
    code: 'unavailable' | 'quantity_changed' | 'price_changed' | 'shipping_unavailable' | 'expired';
    itemId?: string;
    message: string;
}

export interface ShippingQuote {
    id: string;
    label: string;
    cost: Money;
    expiresAt: string;
}

export interface CheckoutSummary {
    cartRevision: string;
    shippingQuote: ShippingQuote;
    totalsByCurrency: Record<CartCurrency, CartTotals>;
    validationErrors: CartValidationError[];
    expiresAt: string;
}

export interface CustomerAddress {
    id: string;
    label: string | null;
    recipient: string;
    lines: string[];
    city: string;
    region: string;
    postalCode: string;
    countryCode: string;
    isDefault: boolean;
}

export interface CustomerOrderSummary {
    number: string;
    placedAt: string;
    statusLabel: string;
    total: Money;
}

export interface CustomerOrderDetail extends CustomerOrderSummary {
    shipments: { carrier: string; statusLabel: string; trackingUrl: string | null }[];
    lines: { sku: string; name: string; quantity: number; unitPrice: Money }[];
}
