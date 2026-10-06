import { Head } from '@inertiajs/react';
import { home } from '@/routes';
import type { PaymentMethodOption } from '@/types';

type Props = {
    paymentMethods: PaymentMethodOption[];
};

/**
 * Point of sale screen. Search and cart (step 2) and the payment dialog
 * (step 3) are composed here; this page only wires them together.
 */
export default function PointOfSale({ paymentMethods }: Props) {
    return (
        <>
            <Head title="PDV" />

            <div className="grid h-full flex-1 gap-4 p-4 lg:grid-cols-[1fr_380px]">
                <section
                    aria-label="Busca de produtos"
                    className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <h2 className="text-sm font-medium text-muted-foreground">
                        Buscar produto
                    </h2>
                </section>

                <aside
                    aria-label="Carrinho"
                    className="flex flex-col rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <h2 className="text-sm font-medium text-muted-foreground">
                        Carrinho
                    </h2>
                    <p className="mt-auto text-xs text-muted-foreground">
                        Formas de pagamento:{' '}
                        {paymentMethods
                            .map((method) => method.label)
                            .join(', ')}
                    </p>
                </aside>
            </div>
        </>
    );
}

PointOfSale.layout = {
    breadcrumbs: [
        {
            title: 'PDV',
            href: home(),
        },
    ],
};
