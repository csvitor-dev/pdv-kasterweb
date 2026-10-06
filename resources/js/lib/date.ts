const dateTimeFormatter = new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
});

/** "06/10/2026, 14:32" */
export function formatDateTime(isoDate: string): string {
    return dateTimeFormatter.format(new Date(isoDate));
}

const timeFormatter = new Intl.DateTimeFormat('pt-BR', { timeStyle: 'short' });

const longDateFormatter = new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'full',
});

/** "14:32" */
export function formatTime(isoDate: string): string {
    return timeFormatter.format(new Date(isoDate));
}

/** "terça-feira, 6 de outubro de 2026", from a calendar date ("2026-10-06"). */
export function formatLongDate(calendarDate: string): string {
    const [year, month, day] = calendarDate.split('-').map(Number);

    return longDateFormatter.format(new Date(year, month - 1, day));
}
