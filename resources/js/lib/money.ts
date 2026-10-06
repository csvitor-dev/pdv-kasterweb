const currencyFormatter = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

/**
 * Formats an integer amount of cents as Brazilian currency: 1890 → "R$ 18,90".
 */
export function formatCents(cents: number): string {
    return currencyFormatter.format(cents / 100);
}

/**
 * Parses what the operator typed ("18,90", "R$ 1.234,5", "20") into cents.
 * Returns null when the text has no valid amount.
 */
export function parseCurrencyToCents(text: string): number | null {
    const normalized = text
        .replace(/[^\d,.-]/g, '')
        .replace(/\./g, '')
        .replace(',', '.');

    if (normalized === '' || normalized === '-') {
        return null;
    }

    const amount = Number(normalized);

    if (!Number.isFinite(amount) || amount < 0) {
        return null;
    }

    return Math.round(amount * 100);
}
