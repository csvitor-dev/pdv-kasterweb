import { ShoppingCart } from 'lucide-react';
import { CartItemRow } from '@/components/pos/cart-item-row';
import type { CartItem } from '@/types';

type Props = {
    items: CartItem[];
    /** Server errors keyed by product uuid. */
    itemErrors?: Record<string, string>;
    onQuantityChange: (productUuid: string, quantity: number) => void;
    onRemove: (productUuid: string) => void;
};

export function Cart({
    items,
    itemErrors = {},
    onQuantityChange,
    onRemove,
}: Props) {
    if (items.length === 0) {
        return (
            <div
                role="status"
                className="flex flex-1 flex-col items-center justify-center gap-3 py-10 text-center text-sm text-muted-foreground"
            >
                <ShoppingCart className="size-8" />
                Carrinho vazio. Busque um produto para começar a venda.
            </div>
        );
    }

    return (
        <ul className="flex-1 divide-y overflow-y-auto" aria-label="Itens">
            {items.map((item) => (
                <CartItemRow
                    key={item.product.uuid}
                    item={item}
                    error={itemErrors[item.product.uuid]}
                    onQuantityChange={(quantity) =>
                        onQuantityChange(item.product.uuid, quantity)
                    }
                    onRemove={() => onRemove(item.product.uuid)}
                />
            ))}
        </ul>
    );
}
