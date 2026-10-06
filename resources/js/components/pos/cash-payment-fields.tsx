import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatCents } from '@/lib/money';
import { cn } from '@/lib/utils';

type Props = {
    totalCents: number;
    amountText: string;
    amountReceivedCents: number | null;
    error?: string;
    onAmountTextChange: (text: string) => void;
};

/** Exact amount plus the next common banknote values above the total. */
function quickAmounts(totalCents: number): number[] {
    const roundUp = (step: number) => Math.ceil(totalCents / step) * step;

    return [
        ...new Set([totalCents, roundUp(1000), roundUp(5000), roundUp(10000)]),
    ];
}

export function CashPaymentFields({
    totalCents,
    amountText,
    amountReceivedCents,
    error,
    onAmountTextChange,
}: Props) {
    const changeCents =
        amountReceivedCents === null ? null : amountReceivedCents - totalCents;
    const isShort = changeCents !== null && changeCents < 0;

    return (
        <div className="space-y-3">
            <div className="grid gap-2">
                <Label htmlFor="amount-received">Valor recebido</Label>
                <div className="relative">
                    <span className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground">
                        R$
                    </span>
                    <Input
                        id="amount-received"
                        inputMode="decimal"
                        autoComplete="off"
                        autoFocus
                        placeholder="0,00"
                        className="h-11 pl-10 text-lg tabular-nums"
                        value={amountText}
                        aria-invalid={isShort || Boolean(error)}
                        onChange={(event) =>
                            onAmountTextChange(event.target.value)
                        }
                    />
                </div>
                <InputError message={error} />
            </div>

            <div className="flex flex-wrap gap-2">
                {quickAmounts(totalCents).map((cents, index) => (
                    <Button
                        key={cents}
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() =>
                            onAmountTextChange(
                                (cents / 100).toFixed(2).replace('.', ','),
                            )
                        }
                    >
                        {index === 0 ? 'Valor exato' : formatCents(cents)}
                    </Button>
                ))}
            </div>

            <div
                aria-live="polite"
                className={cn(
                    'flex items-baseline justify-between rounded-lg px-4 py-3',
                    isShort ? 'bg-destructive/10 text-destructive' : 'bg-muted',
                )}
            >
                <span className="text-sm">{isShort ? 'Faltam' : 'Troco'}</span>
                <span className="text-xl font-bold tabular-nums">
                    {changeCents === null
                        ? '—'
                        : formatCents(Math.abs(changeCents))}
                </span>
            </div>
        </div>
    );
}
