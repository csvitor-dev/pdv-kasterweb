import { useCallback, useMemo, useReducer } from 'react';
import type { CartItem, Product } from '@/types';

type CartAction =
    | { type: 'add'; product: Product }
    | { type: 'setQuantity'; productUuid: string; quantity: number }
    | { type: 'remove'; productUuid: string }
    | { type: 'clear' };

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

/**
 * Cart state for the point of sale. The total is for display only: the
 * server recomputes every price when the sale is created.
 */
export function useCart(): Cart {
    const [items, dispatch] = useReducer(cartReducer, []);

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

    const clear = useCallback(() => dispatch({ type: 'clear' }), []);

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
