import { router, useHttp } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import SearchProductsController from '@/actions/App/Http/Controllers/Api/Products/SearchProductsController';
import { login } from '@/routes';
import type { Product } from '@/types';

export type ProductSearchStatus =
    | 'idle'
    | 'loading'
    | 'results'
    | 'empty'
    | 'error';

type SearchResponse = { data: Product[] };

const DEBOUNCE_MS = 300;

/**
 * Searches sellable products as the operator types, cancelling stale requests.
 * `searchNow` skips the debounce, for barcode scanners that type and press
 * Enter faster than the debounce window.
 */
export function useProductSearch() {
    const request = useHttp<Record<string, never>, SearchResponse>();
    const [term, setTerm] = useState('');
    const [status, setStatus] = useState<ProductSearchStatus>('idle');
    const [products, setProducts] = useState<Product[]>([]);
    const debounceTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    function clearDebounce(): void {
        if (debounceTimer.current) {
            clearTimeout(debounceTimer.current);
            debounceTimer.current = null;
        }
    }

    async function searchNow(searchTerm: string): Promise<Product[]> {
        clearDebounce();
        request.cancel();

        const trimmedTerm = searchTerm.trim();

        if (trimmedTerm === '') {
            setProducts([]);
            setStatus('idle');

            return [];
        }

        setStatus('loading');

        try {
            const response = await request.get(
                SearchProductsController.url({
                    query: { search: trimmedTerm },
                }),
                {
                    onError: () => setStatus('error'),
                    onHttpException: (httpResponse) => {
                        if (httpResponse.status === 401) {
                            router.visit(login());
                        }

                        setStatus('error');
                    },
                    onNetworkError: () => setStatus('error'),
                },
            );

            if (!response) {
                return [];
            }

            setProducts(response.data);
            setStatus(response.data.length > 0 ? 'results' : 'empty');

            return response.data;
        } catch {
            return [];
        }
    }

    /** Keeps the debounce effect independent of the per-render request object. */
    const searchNowRef = useRef(searchNow);

    useEffect(() => {
        searchNowRef.current = searchNow;
    });

    useEffect(() => {
        debounceTimer.current = setTimeout(
            () => void searchNowRef.current(term),
            DEBOUNCE_MS,
        );

        return clearDebounce;
    }, [term]);

    function reset(): void {
        clearDebounce();
        request.cancel();
        setTerm('');
        setProducts([]);
        setStatus('idle');
    }

    return {
        term,
        setTerm,
        status,
        products,
        searchNow,
        retry: () => void searchNow(term),
        reset,
    };
}
