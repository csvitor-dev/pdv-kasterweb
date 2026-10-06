import { formatDateTime } from '@/lib/date';
import { formatCents } from '@/lib/money';
import type { Order } from '@/types';

type Props = {
    order: Order;
};

/**
 * Receipt of a finalized sale. Every value comes from the server, including
 * the unit prices recorded at the time of sale.
 */
export function OrderReceipt({ order }: Props) {
    return (
        <article
            aria-labelledby="receipt-title"
            className="mx-auto w-full max-w-md rounded-xl border bg-card p-6 font-mono text-sm print:border-0 print:p-0"
        >
            <header className="space-y-1 border-b border-dashed pb-4 text-center">
                <h1 id="receipt-title" className="text-base font-bold">
                    Comprovante de venda
                </h1>
                <p className="text-xs text-muted-foreground">
                    {formatDateTime(order.created_at)} · Operador:{' '}
                    {order.operator}
                </p>
                <p className="text-xs break-all text-muted-foreground">
                    Venda {order.uuid}
                </p>
            </header>

            <table className="my-4 w-full">
                <caption className="sr-only">Itens da venda</caption>
                <thead className="text-xs text-muted-foreground">
                    <tr>
                        <th scope="col" className="pb-2 text-left font-normal">
                            Item
                        </th>
                        <th scope="col" className="pb-2 text-right font-normal">
                            Subtotal
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {order.items.map((item, index) => (
                        <tr key={index} className="align-top">
                            <td className="py-1 pr-2">
                                <span className="block">
                                    {item.product_name}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {item.quantity} ×{' '}
                                    {formatCents(item.unit_price_cents)}
                                </span>
                            </td>
                            <td className="py-1 text-right tabular-nums">
                                {formatCents(item.subtotal_cents)}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <dl className="space-y-1 border-t border-dashed pt-4">
                <ReceiptLine
                    label="Total"
                    value={formatCents(order.total_cents)}
                    emphasis
                />
                <ReceiptLine
                    label="Forma de pagamento"
                    value={order.payment_method.label}
                />
                {order.amount_received_cents !== null && (
                    <ReceiptLine
                        label="Valor recebido"
                        value={formatCents(order.amount_received_cents)}
                    />
                )}
                {order.change_cents !== null && (
                    <ReceiptLine
                        label="Troco"
                        value={formatCents(order.change_cents)}
                    />
                )}
            </dl>
        </article>
    );
}

function ReceiptLine({
    label,
    value,
    emphasis = false,
}: {
    label: string;
    value: string;
    emphasis?: boolean;
}) {
    return (
        <div
            className={
                emphasis
                    ? 'flex justify-between text-base font-bold'
                    : 'flex justify-between'
            }
        >
            <dt>{label}</dt>
            <dd className="tabular-nums">{value}</dd>
        </div>
    );
}
