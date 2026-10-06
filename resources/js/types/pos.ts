/**
 * Mirrors the API resources. Every money value is an integer amount of cents.
 */

export type Product = {
    uuid: string;
    code: string;
    name: string;
    price_cents: number;
    stock: number;
};

export type CartItem = {
    product: Product;
    quantity: number;
};

/** Backed by the PaymentMethod enum on the server; never hardcode the values. */
export type PaymentMethodOption = {
    value: number;
    label: string;
    /** True for cash: the operator must inform the amount received. */
    requires_amount_received: boolean;
};

export type OrderItem = {
    product_name: string;
    unit_price_cents: number;
    quantity: number;
    subtotal_cents: number;
};

export type Order = {
    uuid: string;
    payment_method: PaymentMethodOption;
    total_cents: number;
    amount_received_cents: number | null;
    change_cents: number | null;
    operator: string;
    items: OrderItem[];
    created_at: string;
};

/** Order as listed in the day's sales: no items, only how many there are. */
export type OrderSummary = Omit<Order, 'items'> & {
    items_count: number;
};

export type DailySalesSummary = {
    orders_count: number;
    total_cents: number;
};
