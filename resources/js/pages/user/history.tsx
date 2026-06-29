import { Head, Link, usePage } from '@inertiajs/react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import type { PastGameSession, SharedData } from '@/types';

type HistoryProps = {
    sessions: PastGameSession[];
};

export default function History({ sessions }: HistoryProps) {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Past games" />
            <MobileLayout
                title="Past games"
                subtitle={
                    auth.user
                        ? 'Completed sessions you played in.'
                        : 'Log in to see your game history.'
                }
            >
                {!auth.user ? (
                    <div className="flex flex-1 flex-col justify-center gap-3">
                        <p className="text-center text-sm text-emerald-100/70">
                            Sign in to browse your completed mahjong sessions.
                        </p>
                        <Link
                            href="/login"
                            className="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-base font-semibold text-white shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-400 active:scale-[0.98]"
                        >
                            Log in
                        </Link>
                    </div>
                ) : sessions.length === 0 ? (
                    <div className="flex flex-1 flex-col items-center justify-center rounded-2xl border border-dashed border-white/15 bg-white/5 p-8 text-center">
                        <p className="text-4xl">📜</p>
                        <p className="mt-4 text-base text-emerald-100/70">
                            No completed games yet. Finish a session to see it
                            here.
                        </p>
                        <Link
                            href="/user"
                            className="mt-6 inline-flex min-h-11 items-center text-sm font-medium text-emerald-300 hover:text-emerald-200"
                        >
                            Back to my table
                        </Link>
                    </div>
                ) : (
                    <ul className="flex flex-1 flex-col gap-3">
                        {sessions.map((session) => (
                            <li key={session.id}>
                                <Link
                                    href={`/sessions/${session.id}`}
                                    className="block rounded-2xl border border-white/10 bg-white/5 p-4 transition active:bg-white/10"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <h2 className="truncate font-semibold text-white">
                                                {session.name}
                                            </h2>
                                            <p className="mt-1 text-sm text-emerald-100/60">
                                                {session.player_count} player
                                                {session.player_count === 1
                                                    ? ''
                                                    : 's'}{' '}
                                                · Master{' '}
                                                {session.room_master_username}
                                            </p>
                                        </div>
                                        {session.viewer_total_money !==
                                        null ? (
                                            <span
                                                className={`shrink-0 text-sm font-bold ${
                                                    session.viewer_total_money >=
                                                    0
                                                        ? 'text-emerald-300'
                                                        : 'text-red-300'
                                                }`}
                                            >
                                                {session.viewer_total_money >= 0
                                                    ? '+'
                                                    : ''}
                                                {session.viewer_total_money}
                                            </span>
                                        ) : null}
                                    </div>
                                    {session.completed_at ? (
                                        <p className="mt-3 text-xs text-emerald-100/50">
                                            {new Date(
                                                session.completed_at,
                                            ).toLocaleString()}
                                        </p>
                                    ) : null}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </MobileLayout>
        </>
    );
}
