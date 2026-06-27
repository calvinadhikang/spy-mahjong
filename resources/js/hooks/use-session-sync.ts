import { router, usePoll } from '@inertiajs/react';
import { useEffect, useMemo } from 'react';

import type { GameSession } from '@/types';

const POLL_INTERVAL_MS = 4000;

const ACTIVE_STATUSES: GameSession['status'][] = [
    'waiting',
    'in_progress',
    'finishing',
];

type UseSessionSyncOptions = {
    shouldSync: boolean;
    only: readonly string[];
    paused?: boolean;
};

export function isActiveGameSessionStatus(
    status: GameSession['status'],
): boolean {
    return ACTIVE_STATUSES.includes(status);
}

export function useSessionSync({
    shouldSync,
    only,
    paused = false,
}: UseSessionSyncOptions) {
    const onlyKey = only.join(',');

    const reloadOptions = useMemo(
        () => ({
            only: onlyKey.split(','),
            preserveScroll: true,
            preserveState: true,
        }),
        [onlyKey],
    );

    const { stop, start } = usePoll(
        POLL_INTERVAL_MS,
        reloadOptions,
        { keepAlive: false, autoStart: false, mode: 'cancel' },
    );

    useEffect(() => {
        if (!shouldSync || paused) {
            stop();

            return;
        }

        start();

        const resync = () => {
            if (document.visibilityState === 'visible' && navigator.onLine) {
                router.reload(reloadOptions);
            }
        };

        document.addEventListener('visibilitychange', resync);
        window.addEventListener('online', resync);

        return () => {
            stop();
            document.removeEventListener('visibilitychange', resync);
            window.removeEventListener('online', resync);
        };
    }, [shouldSync, paused, start, stop, reloadOptions]);
}
