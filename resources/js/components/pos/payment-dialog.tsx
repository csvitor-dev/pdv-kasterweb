import { useState } from 'react';
import InputError from '@/components/input-error';
import { CashPaymentFields } from '@/components/pos/cash-payment-fields';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { formatCents, parseCurrencyToCents } from '@/lib/money';
import type { PaymentMethodOption } from '@/types';

export type PaymentSelection = {
    paymentMethod: number;
    amountReceivedCents: number | null;
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    totalCents: number;
    paymentMethods: PaymentMethodOption[];
    processing: boolean;
    errors: Record<string, string>;
    onConfirm: (selection: PaymentSelection) => void;
};

export function PaymentDialog({
    open,
    onOpenChange,
    totalCents,
    paymentMethods,
    processing,
    errors,
    onConfirm,
}: Props) {
    const [paymentMethod, setPaymentMethod] = useState(
        paymentMethods[0]?.value,
    );
    const [amountText, setAmountText] = useState('');

    const selectedMethod = paymentMethods.find(
        (method) => method.value === paymentMethod,
    );
    const isCash = selectedMethod?.requires_amount_received ?? false;
    const amountReceivedCents = parseCurrencyToCents(amountText);
    const canConfirm =
        !processing &&
        selectedMethod !== undefined &&
        (!isCash ||
            (amountReceivedCents !== null &&
                amountReceivedCents >= totalCents));

    function handleOpenChange(nextOpen: boolean): void {
        if (processing) {
            return;
        }

        if (!nextOpen) {
            setAmountText('');
        }

        onOpenChange(nextOpen);
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent>
                <form
                    className="space-y-6"
                    onSubmit={(event) => {
                        event.preventDefault();

                        if (canConfirm) {
                            onConfirm({
                                paymentMethod: selectedMethod.value,
                                amountReceivedCents: isCash
                                    ? amountReceivedCents
                                    : null,
                            });
                        }
                    }}
                >
                    <DialogHeader>
                        <DialogTitle>Finalizar venda</DialogTitle>
                        <DialogDescription>
                            Total a pagar:{' '}
                            <strong className="text-foreground tabular-nums">
                                {formatCents(totalCents)}
                            </strong>
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-2">
                        <Label id="payment-method-label">
                            Forma de pagamento
                        </Label>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            aria-labelledby="payment-method-label"
                            className="grid w-full grid-cols-2 gap-2"
                            value={String(paymentMethod)}
                            onValueChange={(value) => {
                                if (value !== '') {
                                    setPaymentMethod(Number(value));
                                }
                            }}
                        >
                            {paymentMethods.map((method) => (
                                <ToggleGroupItem
                                    key={method.value}
                                    value={String(method.value)}
                                    className="h-11 rounded-md data-[state=on]:border-primary data-[state=on]:bg-primary data-[state=on]:text-primary-foreground data-[variant=outline]:border-l"
                                >
                                    {method.label}
                                </ToggleGroupItem>
                            ))}
                        </ToggleGroup>
                        <InputError message={errors.payment_method} />
                    </div>

                    {isCash && (
                        <CashPaymentFields
                            totalCents={totalCents}
                            amountText={amountText}
                            amountReceivedCents={amountReceivedCents}
                            error={errors.amount_received_cents}
                            onAmountTextChange={setAmountText}
                        />
                    )}

                    <InputError message={errors.items} />

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={processing}
                            onClick={() => handleOpenChange(false)}
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={!canConfirm}>
                            {processing && <Spinner />}
                            Confirmar pagamento
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
