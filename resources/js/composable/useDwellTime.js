import { computed } from 'vue';
import moment from 'moment';

/**
 * @param {Ref|string|Function} datetime - ISO string, ref, or getter
 * @returns {ComputedRef<string|null>}
 */
export function useDwellTime(datetime) {
    return computed(() => {
        const raw = typeof datetime === 'function' ? datetime() : unref(datetime);
        if (!raw) return null;

        const from = moment(raw);
        const now = moment();
        const duration = moment.duration(now.diff(from));

        const days = duration.days();
        const hours = duration.hours();
        const minutes = duration.minutes();

        let result = '';
        if (days) result += `${days} day${days > 1 ? 's' : ''} `;
        if (hours) result += `${hours} hour${hours > 1 ? 's' : ''} `;
        if (minutes) result += `${minutes} min${minutes > 1 ? 's' : ''}`;

        return result.trim() ? result.trim() + ' ago' : 'Just now';
    });
}
