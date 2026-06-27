import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { ActiveSessionBlockModal } from '@/components/sessions/active-session-block-modal';
import type { SharedData } from '@/types';

type UseActiveSessionBlockOptions = {
    initiallyOpen?: boolean;
};

export function useActiveSessionBlock({
    initiallyOpen = false,
}: UseActiveSessionBlockOptions = {}) {
    const { activeSession, flash } = usePage<
        SharedData & {
            flash?: { active_session_block?: boolean };
            showActiveSessionBlock?: boolean;
        }
    >().props;

    const [open, setOpen] = useState(initiallyOpen);

    useEffect(() => {
        if (initiallyOpen || flash?.active_session_block) {
            setOpen(true);
        }
    }, [initiallyOpen, flash?.active_session_block]);

    const modal = activeSession ? (
        <ActiveSessionBlockModal
            session={activeSession}
            open={open}
            onClose={() => setOpen(false)}
        />
    ) : null;

    return {
        open,
        openBlock: () => setOpen(true),
        closeBlock: () => setOpen(false),
        modal,
        activeSession,
    };
}
