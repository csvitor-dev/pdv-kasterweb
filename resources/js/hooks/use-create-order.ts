import { router, useHttp } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import StoreOrderController from '@/actions/App/Http/Controllers/Api/Orders/StoreOrderController';
import { login } from '@/routes';
import type { CartItem, Order } from '@/types';

type CreateOrderPayload = {
    items: { product_uuid: string; quantity: number }[];
    payment_method: number;
    amount_received_cents: number | null;
};

export type CreateOrderInput = {
    items: CartItem[];
    paymentMethod: number;
    amountReceivedCents: number | null;
};

export type CreateOrderErrors = {
    /** Errors that belong to the payment form (method, amount received). */
    payment: Record<string, string>;
    /** Item errors keyed by product uuid, shown on the cart rows. */
    items: Record<string, string>;
};

export type CreateOrderResult =
    | 'created'
    | 'invalid-items'
    | 'invalid-payment'
    | 'failed';

const ITEM_ERROR_KEY = /^items\.(\d+)\./;

/**
 * Sends the sale to the API. Only product ids and quantities are sent:
 * prices, totals and change are computed by the server.
 */
export function useCreateOrder({
    onCreated,
}: {
    onCreated: (order: Order) => void;
}) {
    const request = useHttp<CreateOrderPayload, { data: Order }>({
        items: [],
        payment_method: 0,
        amount_received_cents: null,
    });
    const [submittedUuids, setSubmittedUuids] = useState<string[]>([]);

    async function submit(input: CreateOrderInput): Promise<CreateOrderResult> {
        const payload: CreateOrderPayload = {
            items: input.items.map((item) => ({
                product_uuid: item.product.uuid,
                quantity: item.quantity,
            })),
            payment_method: input.paymentMethod,
            amount_received_cents: input.amountReceivedCents,
        };

        setSubmittedUuids(input.items.map((item) => item.product.uuid));
        request.clearErrors();
        request.transform(() => payload);

        let result: CreateOrderResult = 'failed';

        try {
            const response = await request.post(StoreOrderController.url(), {
                onError: (validationErrors) => {
                    result = Object.keys(validationErrors).some((key) =>
                        ITEM_ERROR_KEY.test(key),
                    )
                        ? 'invalid-items'
                        : 'invalid-payment';
                },
                onHttpException: (httpResponse) => {
                    if (httpResponse.status === 401) {
                        router.visit(login());

                        return;
                    }

                    toast.error(
                        'Não foi possível finalizar a venda. Tente novamente.',
                    );
                },
                onNetworkError: () => {
                    toast.error(
                        'Sem conexão com o servidor. Verifique a rede e tente novamente.',
                    );
                },
            });

            if (response) {
                onCreated(response.data);
                result = 'created';
            }
        } catch {
            result = 'failed';
        }

        return result;
    }

    const errors: CreateOrderErrors = { payment: {}, items: {} };

    for (const [key, message] of Object.entries(
        request.errors as Record<string, string>,
    )) {
        const itemIndex = ITEM_ERROR_KEY.exec(key)?.[1];
        const productUuid =
            itemIndex === undefined
                ? undefined
                : submittedUuids[Number(itemIndex)];

        if (productUuid) {
            errors.items[productUuid] = message;
        } else {
            errors.payment[key] = message;
        }
    }

    return {
        submit,
        processing: request.processing,
        errors,
        clearErrors: () => request.clearErrors(),
    };
}
