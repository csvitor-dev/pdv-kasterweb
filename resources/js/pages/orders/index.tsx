import { Head, Link } from '@inertiajs/react';
import { ReceiptText } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { formatLongDate, formatTime } from '@/lib/date';
import { formatCents } from '@/lib/money';
import { home } from '@/routes';
import { index, show } from '@/routes/orders';
import type { DailySalesSummary, OrderSummary } from '@/types';

type Props = {
    date: string;
    orders: OrderSummary[];
    summary: DailySalesSummary;
};

export default function TodayOrders({ date, orders, summary }: Props) {
    return (
        <>
            <Head title="Vendas do dia" />

            <div className="flex flex-1 flex-col gap-6 p-4">
                <header>
                    <h1 className="text-lg font-semibold">Vendas do dia</h1>
                    <p className="text-sm text-muted-foreground first-letter:uppercase">
                        {formatLongDate(date)}
                    </p>
                </header>

                <dl className="grid gap-4 sm:grid-cols-2">
                    <SummaryTile
                        label="Vendas"
                        value={String(summary.orders_count)}
                    />
                    <SummaryTile
                        label="Total vendido"
                        value={formatCents(summary.total_cents)}
                    />
                </dl>

                {orders.length === 0 ? (
                    <div
                        role="status"
                        className="flex flex-col items-center gap-3 rounded-xl border py-12 text-center text-sm text-muted-foreground"
                    >
                        <ReceiptText className="size-8" />
                        Nenhuma venda finalizada hoje.
                        <Button asChild size="sm">
                            <Link href={home()}>Ir para o PDV</Link>
                        </Button>
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-xl border">
                        <table className="w-full text-sm">
                            <caption className="sr-only">
                                Vendas finalizadas hoje
                            </caption>
                            <thead className="border-b bg-muted/50 text-left text-xs text-muted-foreground">
                                <tr>
                                    <th
                                        scope="col"
                                        className="px-4 py-2 font-medium"
                                    >
                                        Hora
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-4 py-2 font-medium"
                                    >
                                        Produtos
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-4 py-2 font-medium"
                                    >
                                        Pagamento
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-4 py-2 font-medium"
                                    >
                                        Operador
                                    </th>
                                    <th
                                        scope="col"
                                        className="px-4 py-2 text-right font-medium"
                                    >
                                        Total
                                    </th>
                                    <th scope="col" className="px-4 py-2">
                                        <span className="sr-only">Ações</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {orders.map((order) => (
                                    <tr key={order.uuid}>
                                        <td className="px-4 py-3 tabular-nums">
                                            {formatTime(order.created_at)}
                                        </td>
                                        <td className="px-4 py-3 tabular-nums">
                                            {order.items_count}
                                        </td>
                                        <td className="px-4 py-3">
                                            {order.payment_method.label}
                                        </td>
                                        <td className="px-4 py-3">
                                            {order.operator}
                                        </td>
                                        <td className="px-4 py-3 text-right font-medium tabular-nums">
                                            {formatCents(order.total_cents)}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button
                                                asChild
                                                variant="ghost"
                                                size="sm"
                                            >
                                                <Link href={show(order.uuid)}>
                                                    Comprovante
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}

TodayOrders.layout = {
    breadcrumbs: [
        {
            title: 'Vendas do dia',
            href: index(),
        },
    ],
};

function SummaryTile({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border p-4">
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd className="text-2xl font-bold tabular-nums">{value}</dd>
        </div>
    );
}
