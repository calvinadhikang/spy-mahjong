import { Head, Link, usePage } from '@inertiajs/react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import { StatusBadge } from '@/components/ui/status-badge';
import { useActiveSessionBlock } from '@/hooks/use-active-session-block';
import {
    isActiveGameSessionStatus,
    useSessionSync,
} from '@/hooks/use-session-sync';
import type { GameSession, SharedData } from '@/types';

type DashboardProps = {
    activeSession: GameSession | null;
};

export default function Dashboard({ activeSession }: DashboardProps) {
    const { auth } = usePage<SharedData>().props;
    const { modal } = useActiveSessionBlock();

    useSessionSync({
        shouldSync:
            activeSession !== null &&
            isActiveGameSessionStatus(activeSession.status),
        only: ['activeSession'],
    });

    return (
        <>
            <Head title="My table" />
            {modal}
            <MobileLayout
                title="My table"
                subtitle={
                    auth.user
                        ? `Hi, ${auth.user.name}.`
                        : 'Log in to manage your game sessions.'
                }
            >
                {!auth.user ? (
                    <div className="flex flex-1 flex-col justify-center gap-3">
                        <p className="text-center text-sm text-emerald-100/70">
                            Sign in to see your active session or create a new
                            one.
                        </p>
                        <Link
                            href="/login"
                            className="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-base font-semibold text-white shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-400 active:scale-[0.98]"
                        >
                            Log in
                        </Link>
                    </div>
                ) : activeSession ? (
                    <div className="flex flex-1 flex-col gap-5">
                        <div className="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <div className="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <p className="text-sm text-emerald-100/60">
                                        Active session
                                    </p>
                                    <h2 className="mt-1 text-xl font-bold">
                                        {activeSession.name}
                                    </h2>
                                </div>
                                <StatusBadge
                                    status={activeSession.status}
                                    label={activeSession.status_label}
                                />
                            </div>

                            <p className="text-sm text-emerald-100/70">
                                {activeSession.players.length} player
                                {activeSession.players.length === 1
                                    ? ''
                                    : 's'}{' '}
                                · Room master{' '}
                                <span className="font-medium text-white">
                                    {activeSession.room_master.name}
                                </span>
                            </p>

                            <Link
                                href={`/sessions/${activeSession.id}`}
                                className="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-base font-semibold text-white transition hover:bg-emerald-400 active:scale-[0.98]"
                            >
                                View session
                            </Link>
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-1 flex-col justify-center gap-4">
                        <div className="rounded-2xl border border-dashed border-white/15 bg-white/5 p-6 text-center">
                            <p className="text-4xl">🀄</p>
                            <p className="mt-4 text-base text-emerald-100/70">
                                You are not in a session yet. Create one and
                                invite players to the table.
                            </p>
                        </div>

                        <Link
                            href="/sessions/create"
                            className="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-base font-semibold text-white shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-400 active:scale-[0.98]"
                        >
                            Create session
                        </Link>
                    </div>
                )}
            </MobileLayout>
        </>
    );
}
