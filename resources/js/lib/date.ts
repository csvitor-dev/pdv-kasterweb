const dateTimeFormatter = new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
});

/** "06/10/2026, 14:32" */
export function formatDateTime(isoDate: string): string {
    return dateTimeFormatter.format(new Date(isoDate));
}
