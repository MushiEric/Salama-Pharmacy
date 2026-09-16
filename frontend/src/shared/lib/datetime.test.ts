import { describe, expect, it } from 'vitest';
import { APP_TIMEZONE, formatTzs, toLocalDate } from './datetime';

describe('datetime', () => {
    it('converts UTC instants into Africa/Dar_es_Salaam', () => {
        const local = toLocalDate('2026-01-15T21:00:00.000Z');

        expect(local.timeZone).toBe(APP_TIMEZONE);
        expect(local.getHours()).toBe(0);
    });

    it('formats TZS without binary float math', () => {
        expect(formatTzs('1500.50')).toContain('1,500.50');
    });
});
