import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Cart } from '@/components/pos/cart';
import { CartSummary } from '@/components/pos/cart-summary';
import { PaymentDialog } from '@/components/pos/payment-dialog';
import type { PaymentSelection } from '@/components/pos/payment-dialog';
import { ProductSearch } from '@/components/pos/product-search';
import { Button } from '@/components/ui/button';
import { useCart } from '@/hooks/use-cart';
import { useCreateOrder } from '@/hooks/use-create-order';
import { home } from '@/routes';
import { show } from '@/routes/orders';
import type { Auth, PaymentMethodOption } from '@/types';

type Props = {
    paymentMethods: PaymentMethodOption[];
};

export default function PointOfSale({ paymentMethods }: Props) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const cart = useCart(`pos:cart:${auth.user.id}`);
    const [isPaymentOpen, setPaymentOpen] = useState(false);
    const order = useCreateOrder({
        onCreated: (createdOrder) => {
            cart.clear();
            router.visit(show(createdOrder.uuid));
        },
    });

    async function confirmPayment(selection: PaymentSelection): Promise<void> {
        const result = await order.submit({ items: cart.items, ...selection });

        if (result === 'invalid-items') {
            setPaymentOpen(false);
            toast.error(
                'Alguns itens não podem ser vendidos. Revise o carrinho.',
            );
        }
    }

    return (
        <>
            <Head title="PDV" />

            <div className="grid flex-1 gap-4 p-4 lg:grid-cols-[1fr_400px]">
                <section
                    aria-label="Busca de produtos"
                    className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <ProductSearch onSelect={cart.add} />
                </section>

                <aside
                    aria-label="Carrinho"
                    className="flex max-h-[calc(100svh-6rem)] flex-col rounded-xl border border-sidebar-border/70 p-4 lg:sticky lg:top-4 dark:border-sidebar-border"
                >
                    <h2 className="mb-2 font-semibold">Carrinho</h2>

                    <Cart
                        items={cart.items}
                        itemErrors={order.errors.items}
                        onQuantityChange={cart.setQuantity}
                        onRemove={cart.remove}
                    />

                    <CartSummary
                        itemCount={cart.itemCount}
                        totalCents={cart.totalCents}
                    >
                        <Button
                            size="lg"
                            className="w-full"
                            disabled={cart.isEmpty}
                            onClick={() => {
                                order.clearErrors();
                                setPaymentOpen(true);
                            }}
                        >
                            Finalizar venda
                        </Button>
                    </CartSummary>
                </aside>
            </div>

            <PaymentDialog
                open={isPaymentOpen}
                onOpenChange={setPaymentOpen}
                totalCents={cart.totalCents}
                paymentMethods={paymentMethods}
                processing={order.processing}
                errors={order.errors.payment}
                onConfirm={(selection) => void confirmPayment(selection)}
            />
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
