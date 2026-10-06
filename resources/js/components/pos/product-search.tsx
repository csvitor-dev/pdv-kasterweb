import { Search } from 'lucide-react';
import { useRef } from 'react';
import { toast } from 'sonner';
import { ProductSearchResults } from '@/components/pos/product-search-results';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { useProductSearch } from '@/hooks/use-product-search';
import type { Product } from '@/types';

type Props = {
    /** Returns false when the product could not be added (stock limit). */
    onSelect: (product: Product) => boolean;
};

export function ProductSearch({ onSelect }: Props) {
    const search = useProductSearch();
    const inputRef = useRef<HTMLInputElement>(null);

    function select(product: Product): void {
        if (!onSelect(product)) {
            toast.warning(
                `Estoque máximo de ${product.name} já está no carrinho.`,
            );

            return;
        }

        search.reset();
        inputRef.current?.focus();
    }

    /** Barcode flow: Enter adds the product whose code matches exactly. */
    async function handleEnter(): Promise<void> {
        const products = await search.searchNow(search.term);
        const exactMatch = products.find(
            (product) => product.code === search.term.trim(),
        );

        if (exactMatch) {
            select(exactMatch);
        } else if (products.length === 1) {
            select(products[0]);
        }
    }

    return (
        <div className="flex flex-col gap-4">
            <div className="relative">
                <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    ref={inputRef}
                    type="search"
                    autoFocus
                    autoComplete="off"
                    aria-label="Buscar produto por nome ou código"
                    placeholder="Buscar por nome ou código de barras"
                    className="h-11 px-9 text-base"
                    maxLength={100}
                    value={search.term}
                    onChange={(event) => search.setTerm(event.target.value)}
                    onKeyDown={(event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            void handleEnter();
                        }

                        if (event.key === 'Escape') {
                            search.reset();
                        }
                    }}
                />
                {search.status === 'loading' && (
                    <Spinner className="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground" />
                )}
            </div>

            <ProductSearchResults
                status={search.status}
                term={search.term}
                products={search.products}
                onSelect={select}
                onRetry={search.retry}
            />
        </div>
    );
}
