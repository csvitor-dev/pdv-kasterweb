import { Head } from '@inertiajs/react';
import { Cart } from '@/components/pos/cart';
import { CartSummary } from '@/components/pos/cart-summary';
import { ProductSearch } from '@/components/pos/product-search';
import { useCart } from '@/hooks/use-cart';
import { home } from '@/routes';

export default function PointOfSale() {
    const cart = useCart();

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
                        onQuantityChange={cart.setQuantity}
                        onRemove={cart.remove}
                    />

                    <CartSummary
                        itemCount={cart.itemCount}
                        totalCents={cart.totalCents}
                    />
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
