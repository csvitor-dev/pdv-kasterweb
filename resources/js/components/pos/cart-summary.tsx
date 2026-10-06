import type { ReactNode } from 'react';
import { formatCents } from '@/lib/money';

type Props = {
    itemCount: number;
    totalCents: number;
    /** Checkout action, rendered below the total. */
    children?: ReactNode;
};

export function CartSummary({ itemCount, totalCents, children }: Props) {
    return (
        <div className="space-y-4 border-t pt-4">
            <div className="flex items-baseline justify-between">
                <span className="text-sm text-muted-foreground">
                    {itemCount} {itemCount === 1 ? 'item' : 'itens'}
                </span>
                <span className="text-right">
                    <span className="block text-xs text-muted-foreground">
                        Total
                    </span>
                    <span
                        className="text-2xl font-bold tabular-nums"
                        aria-live="polite"
                    >
                        {formatCents(totalCents)}
                    </span>
                </span>
            </div>
            {children}
        </div>
    );
}
