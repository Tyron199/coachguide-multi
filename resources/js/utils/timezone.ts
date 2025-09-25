/**
 * Timezone utility functions
 */

export interface TimezoneOption {
    value: string;
    label: string;
    offset: string;
}

/**
 * Get all available timezones from the browser
 */
export function getAllTimezones(): TimezoneOption[] {
    // Get all supported timezones from the browser
    const allTimezones = Intl.supportedValuesOf('timeZone');

    // Create timezone options with formatted labels
    return allTimezones.map(timezone => {
        // Create a readable label by replacing underscores with spaces
        // and formatting the timezone name
        const label = timezone
            .replace(/_/g, ' ')
            .split('/')
            .map(part => part.charAt(0).toUpperCase() + part.slice(1))
            .join(' - ');

        return {
            value: timezone,
            label: `${label} (${timezone})`,
            offset: '' // Keep for interface compatibility
        };
    }).sort((a, b) => a.label.localeCompare(b.label));
}

/**
 * Get the current user's detected timezone
 */
export function getCurrentTimezone(): string {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    } catch (error) {
        console.warn('Could not detect timezone, falling back to UTC:', error);
        return 'UTC';
    }
}

/**
 * Format timezone for display
 */
export function formatTimezone(timezone: string): string {
    try {
        const now = new Date();
        const formatter = new Intl.DateTimeFormat('en', {
            timeZone: timezone,
            timeZoneName: 'longOffset'
        });

        const parts = formatter.formatToParts(now);
        const offsetPart = parts.find(part => part.type === 'timeZoneName');
        const offset = offsetPart ? offsetPart.value : '';

        const label = timezone.replace(/_/g, ' ').replace('/', ' - ');
        return `${label} (${offset})`;
    } catch (error) {
        return timezone;
    }
}

/**
 * Get user's timezone from the authenticated user data
 */
export function getUserTimezone(): string {
    // Get from user data passed via Inertia
    const user = (window as any)?.page?.props?.auth?.user;
    return user?.timezone || getCurrentTimezone();
}

/**
 * Convert a date to the user's timezone for display
 */
export function toUserTimezone(date: Date | string, userTimezone?: string): Date {
    const timezone = userTimezone || getUserTimezone();
    const dateObj = typeof date === 'string' ? new Date(date) : date;

    // Create a new date in the user's timezone
    const formatter = new Intl.DateTimeFormat('en-CA', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });

    const parts = formatter.formatToParts(dateObj);
    const year = parts.find(p => p.type === 'year')?.value || '';
    const month = parts.find(p => p.type === 'month')?.value || '';
    const day = parts.find(p => p.type === 'day')?.value || '';
    const hour = parts.find(p => p.type === 'hour')?.value || '';
    const minute = parts.find(p => p.type === 'minute')?.value || '';
    const second = parts.find(p => p.type === 'second')?.value || '';

    return new Date(`${year}-${month}-${day}T${hour}:${minute}:${second}`);
}

/**
 * Format a date string for API consumption (YYYY-MM-DD)
 * Takes into account the user's timezone
 */
export function formatDateForAPI(date: Date, userTimezone?: string): string {
    const timezone = userTimezone || getUserTimezone();

    return date.toLocaleDateString('en-CA', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

/**
 * Get today's date in the user's timezone
 */
export function getTodayInUserTimezone(userTimezone?: string): Date {
    const timezone = userTimezone || getUserTimezone();
    const now = new Date();

    // Get today in the user's timezone
    const today = new Date(now.toLocaleString('en-US', { timeZone: timezone }));
    today.setHours(0, 0, 0, 0);

    return today;
}