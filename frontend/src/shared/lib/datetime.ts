import { TZDate } from '@date-fns/tz';
import { format } from 'date-fns';

export const APP_TIMEZONE = 'Africa/Dar_es_Salaam';
export const APP_CURRENCY = 'TZS';

export function toLocalDate(value: Date | string | number): TZDate {
    return new TZDate(new Date(value), APP_TIMEZONE);
}

export function formatLocalDateTime(
    value: Date | string | number,
    pattern = 'dd MMM yyyy HH:mm',
): string {
    return format(toLocalDate(value), pattern);
}

export function formatTzs(amount: string | number): string {
    const numeric = typeof amount === 'number' ? amount : Number(amount);

    return new Intl.NumberFormat('en-TZ', {
        style: 'currency',
        currency: APP_CURRENCY,
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(numeric);
}
