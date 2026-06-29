import { Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import type { ActiveSessionSummary } from '@/types';

type ActiveSessionBlockModalProps = {
    session: ActiveSessionSummary;
    open: boolean;
    onClose: () => void;
};

export function ActiveSessionBlockModal({
    session,
    open,
    onClose,
}: ActiveSessionBlockModalProps) {
    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-end justify-center p-5 sm:items-center">
            <button
                type="button"
                aria-label="Close dialog"
                className="absolute inset-0 animate-modal-backdrop-in bg-emerald-950/80 backdrop-blur-sm"
                onClick={onClose}
            />

            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="active-session-block-title"
                className="relative w-full max-w-md animate-modal-panel-in rounded-2xl border border-white/10 bg-emerald-900 p-6 shadow-2xl shadow-emerald-950/50"
            >
                <div className="mb-4 flex size-12 items-center justify-center rounded-xl border border-amber-300/20 bg-amber-400/10 text-2xl">
                    🀄
                </div>

                <h2
                    id="active-session-block-title"
                    className="text-xl font-bold text-white"
                >
                    Finish your current game first
                </h2>

                <p className="mt-3 text-sm leading-relaxed text-emerald-100/70">
                    You are still in{' '}
                    <span className="font-semibold text-white">
                        {session.name}
                    </span>{' '}
                    ({session.status_label.toLowerCase()}). Complete or leave
                    that session before creating or joining another room.
                </p>

                <div className="mt-6 flex flex-col gap-3">
                    <Link
                        href={`/sessions/${session.id}`}
                        onClick={onClose}
                        className="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-base font-semibold text-white shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-400 active:scale-[0.98]"
                    >
                        Go to session
                    </Link>
                    <Button fullWidth variant="secondary" onClick={onClose}>
                        Close
                    </Button>
                </div>
            </div>
        </div>
    );
}
