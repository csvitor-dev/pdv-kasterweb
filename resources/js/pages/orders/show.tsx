import { Head, setLayoutProps } from '@inertiajs/react';
import { formatCents } from '@/lib/money';
import { home } from '@/routes';
import { show } from '@/routes/orders';
import type { Order } from '@/types';

type Props = {
    order: Order;
};

/**
 * Receipt of a finalized sale. The full receipt layout comes in step 4.
 */
export default function ShowOrder({ order }: Props) {
    setLayoutProps({
        breadcrumbs: [
            { title: 'PDV', href: home() },
            { title: 'Comprovante', href: show(order.uuid) },
        ],
    });

    return (
        <>
            <Head title="Comprovante" />

            <div className="p-4">
                <h1 className="text-lg font-semibold">Comprovante</h1>
                <p className="text-sm text-muted-foreground">
                    Total: {formatCents(order.total_cents)}
                </p>
            </div>
        </>
    );
}
