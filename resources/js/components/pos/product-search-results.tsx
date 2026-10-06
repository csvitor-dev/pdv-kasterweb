import { AlertCircle, PackageSearch, SearchX } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import type { ProductSearchStatus } from '@/hooks/use-product-search';
import { formatCents } from '@/lib/money';
import type { Product } from '@/types';

type Props = {
    status: ProductSearchStatus;
    term: string;
    products: Product[];
    onSelect: (product: Product) => void;
    onRetry: () => void;
};

export function ProductSearchResults({
    status,
    term,
    products,
    onSelect,
    onRetry,
}: Props) {
    if (status === 'idle') {
        return (
            <SearchMessage icon={<PackageSearch />}>
                Digite o nome ou leia o código de barras do produto.
            </SearchMessage>
        );
    }

    if (status === 'loading') {
        return (
            <ul className="space-y-2" aria-busy="true" aria-label="Carregando">
                {[0, 1, 2].map((row) => (
                    <li key={row}>
                        <Skeleton className="h-14 w-full" />
                    </li>
                ))}
            </ul>
        );
    }

    if (status === 'error') {
        return (
            <SearchMessage icon={<AlertCircle className="text-destructive" />}>
                <span>Não foi possível buscar os produtos.</span>
                <Button variant="outline" size="sm" onClick={onRetry}>
                    Tentar novamente
                </Button>
            </SearchMessage>
        );
    }

    if (status === 'empty') {
        return (
            <SearchMessage icon={<SearchX />}>
                Nenhum produto disponível encontrado para “{term.trim()}”.
            </SearchMessage>
        );
    }

    return (
        <ul className="space-y-2" aria-label="Produtos encontrados">
            {products.map((product) => (
                <li key={product.uuid}>
                    <button
                        type="button"
                        onClick={() => onSelect(product)}
                        className="flex w-full items-center justify-between gap-4 rounded-lg border px-4 py-3 text-left transition-colors hover:bg-accent focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                    >
                        <span className="min-w-0">
                            <span className="block truncate font-medium">
                                {product.name}
                            </span>
                            <span className="text-xs text-muted-foreground">
                                {product.code}
                            </span>
                        </span>
                        <span className="flex shrink-0 items-center gap-3">
                            <Badge variant="secondary">
                                {product.stock} em estoque
                            </Badge>
                            <span className="font-semibold tabular-nums">
                                {formatCents(product.price_cents)}
                            </span>
                        </span>
                    </button>
                </li>
            ))}
        </ul>
    );
}

function SearchMessage({
    icon,
    children,
}: {
    icon: React.ReactNode;
    children: React.ReactNode;
}) {
    return (
        <div
            role="status"
            className="flex flex-col items-center gap-3 py-10 text-center text-sm text-muted-foreground [&_svg]:size-8"
        >
            {icon}
            {children}
        </div>
    );
}
