import { useCallback, useEffect, useMemo, useReducer, useState } from 'react';
import type { CartItem, Product } from '@/types';

type CartAction =
    | { type: 'add'; product: Product }
    | { type: 'setQuantity'; productUuid: string; quantity: number }
    | { type: 'remove'; productUuid: string }
    | { type: 'clear' }
    | { type: 'replace'; items: CartItem[] };

function clampQuantity(quantity: number, product: Product): number {
    return Math.min(Math.max(Math.trunc(quantity), 1), product.stock);
}

function cartReducer(items: CartItem[], action: CartAction): CartItem[] {
    switch (action.type) {
        case 'add': {
            const existing = items.find(
                (item) => item.product.uuid === action.product.uuid,
            );

            if (!existing) {
                return [...items, { product: action.product, quantity: 1 }];
            }

            return items.map((item) =>
                item === existing
                    ? {
                          ...item,
                          quantity: clampQuantity(
                              item.quantity + 1,
                              item.product,
                          ),
                      }
                    : item,
            );
        }
        case 'setQuantity':
            return items.map((item) =>
                item.product.uuid === action.productUuid
                    ? {
                          ...item,
                          quantity: clampQuantity(
                              action.quantity,
                              item.product,
                          ),
                      }
                    : item,
            );
        case 'remove':
            return items.filter(
                (item) => item.product.uuid !== action.productUuid,
            );
        case 'clear':
            return [];
        case 'replace':
            return action.items;
    }
}

export type Cart = {
    items: CartItem[];
    totalCents: number;
    itemCount: number;
    isEmpty: boolean;
    /** Returns false when the product is already at its stock limit. */
    add: (product: Product) => boolean;
    setQuantity: (productUuid: string, quantity: number) => void;
    remove: (productUuid: string) => void;
    clear: () => void;
};

function isCartItem(value: unknown): value is CartItem {
    if (typeof value !== 'object' || value === null) {
        return false;
    }

    const { product, quantity } = value as Partial<CartItem>;

    return (
        typeof quantity === 'number' &&
        typeof product?.uuid === 'string' &&
        typeof product.name === 'string' &&
        typeof product.price_cents === 'number' &&
        typeof product.stock === 'number'
    );
}

/**
 * Reads a cart saved by a previous page load. Storage may be unavailable
 * (private mode, blocked site data) or hold stale/foreign data, so anything
 * unexpected results in an empty cart.
 */
function readStoredCart(storageKey: string): CartItem[] {
    try {
        const parsed: unknown = JSON.parse(
            sessionStorage.getItem(storageKey) ?? '[]',
        );

        return Array.isArray(parsed) ? parsed.filter(isCartItem) : [];
    } catch {
        return [];
    }
}

function writeStoredCart(storageKey: string, items: CartItem[]): void {
    try {
        if (items.length === 0) {
            sessionStorage.removeItem(storageKey);
        } else {
            sessionStorage.setItem(storageKey, JSON.stringify(items));
        }
    } catch {
        // Persistence is a convenience; the cart keeps working in memory.
    }
}

/**
 * Cart state for the point of sale, kept in sessionStorage so a reload does
 * not lose the sale in progress. The total is for display only: the server
 * recomputes every price when the sale is created.
 *
 * The stored cart is restored after mount (not during the first render) so
 * the server-rendered HTML, which has no storage, matches the first client
 * render.
 *
 * @param storageKey Per-operator key, so another login in the same tab does
 * not inherit someone else's cart.
 */
export function useCart(storageKey: string): Cart {
    const [items, dispatch] = useReducer(cartReducer, []);
    const [hasRestored, setHasRestored] = useState(false);

    useEffect(() => {
        dispatch({ type: 'replace', items: readStoredCart(storageKey) });
        setHasRestored(true);
    }, [storageKey]);

    useEffect(() => {
        if (hasRestored) {
            writeStoredCart(storageKey, items);
        }
    }, [storageKey, items, hasRestored]);

    const totalCents = useMemo(
        () =>
            items.reduce(
                (total, item) =>
                    total + item.product.price_cents * item.quantity,
                0,
            ),
        [items],
    );

    const itemCount = useMemo(
        () => items.reduce((count, item) => count + item.quantity, 0),
        [items],
    );

    const add = useCallback(
        (product: Product): boolean => {
            const quantityInCart =
                items.find((item) => item.product.uuid === product.uuid)
                    ?.quantity ?? 0;

            if (quantityInCart >= product.stock) {
                return false;
            }

            dispatch({ type: 'add', product });

            return true;
        },
        [items],
    );

    const setQuantity = useCallback(
        (productUuid: string, quantity: number) =>
            dispatch({ type: 'setQuantity', productUuid, quantity }),
        [],
    );

    const remove = useCallback(
        (productUuid: string) => dispatch({ type: 'remove', productUuid }),
        [],
    );

    /** Also drops the stored cart right away, as a page visit may follow. */
    const clear = useCallback(() => {
        writeStoredCart(storageKey, []);
        dispatch({ type: 'clear' });
    }, [storageKey]);

    return {
        items,
        totalCents,
        itemCount,
        isEmpty: items.length === 0,
        add,
        setQuantity,
        remove,
        clear,
    };
}
