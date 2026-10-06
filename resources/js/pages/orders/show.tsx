import { Head, Link, setLayoutProps } from '@inertiajs/react';
import { CircleCheck, Plus, Printer } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { OrderReceipt } from '@/components/orders/order-receipt';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import { show } from '@/routes/orders';
import type { Order } from '@/types';

type Props = {
    order: Order;
};

export default function ShowOrder({ order }: Props) {
    const newSaleLink = useRef<HTMLAnchorElement>(null);

    /** Lets the operator start the next sale with Enter. */
    useEffect(() => {
        newSaleLink.current?.focus();
    }, []);

    setLayoutProps({
        breadcrumbs: [
            { title: 'PDV', href: home() },
            { title: 'Comprovante', href: show(order.uuid) },
        ],
    });

    return (
        <>
            <Head title="Comprovante" />

            <div className="flex flex-1 flex-col items-center gap-6 p-4 print:p-0">
                <p
                    role="status"
                    className="flex items-center gap-2 text-sm font-medium text-green-700 dark:text-green-400 print:hidden"
                >
                    <CircleCheck className="size-5" />
                    Venda finalizada
                </p>

                <OrderReceipt order={order} />

                <div className="flex gap-2 print:hidden">
                    <Button variant="outline" onClick={() => window.print()}>
                        <Printer />
                        Imprimir
                    </Button>
                    <Button asChild>
                        <Link ref={newSaleLink} href={home()}>
                            <Plus />
                            Nova venda
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
