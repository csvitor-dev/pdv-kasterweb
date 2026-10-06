import { Minus, Plus, Trash2 } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatCents } from '@/lib/money';
import type { CartItem } from '@/types';

type Props = {
    item: CartItem;
    error?: string;
    onQuantityChange: (quantity: number) => void;
    onRemove: () => void;
};

export function CartItemRow({
    item,
    error,
    onQuantityChange,
    onRemove,
}: Props) {
    const { product, quantity } = item;

    return (
        <li className="space-y-2 py-3">
            <div className="flex items-start justify-between gap-2">
                <div className="min-w-0">
                    <p className="truncate font-medium">{product.name}</p>
                    <p className="text-xs text-muted-foreground">
                        {formatCents(product.price_cents)} / un.
                    </p>
                </div>
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8 shrink-0 text-muted-foreground hover:text-destructive"
                    aria-label={`Remover ${product.name}`}
                    onClick={onRemove}
                >
                    <Trash2 />
                </Button>
            </div>

            <div className="flex items-center justify-between gap-2">
                <div className="flex items-center gap-1">
                    <Button
                        variant="outline"
                        size="icon"
                        className="size-8"
                        aria-label={`Diminuir quantidade de ${product.name}`}
                        disabled={quantity <= 1}
                        onClick={() => onQuantityChange(quantity - 1)}
                    >
                        <Minus />
                    </Button>
                    <Input
                        type="number"
                        inputMode="numeric"
                        min={1}
                        max={product.stock}
                        aria-label={`Quantidade de ${product.name}`}
                        className="h-8 w-16 text-center tabular-nums"
                        value={quantity}
                        onChange={(event) => {
                            const typedQuantity = event.target.valueAsNumber;

                            if (!Number.isNaN(typedQuantity)) {
                                onQuantityChange(typedQuantity);
                            }
                        }}
                    />
                    <Button
                        variant="outline"
                        size="icon"
                        className="size-8"
                        aria-label={`Aumentar quantidade de ${product.name}`}
                        disabled={quantity >= product.stock}
                        onClick={() => onQuantityChange(quantity + 1)}
                    >
                        <Plus />
                    </Button>
                </div>
                <span className="font-semibold tabular-nums">
                    {formatCents(product.price_cents * quantity)}
                </span>
            </div>

            <InputError message={error} />
        </li>
    );
}
