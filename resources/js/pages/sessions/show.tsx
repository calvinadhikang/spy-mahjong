import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import { PlayerSearch } from '@/components/sessions/player-search';
import { SessionMoneySettlement } from '@/components/sessions/session-money-settlement';
import { Button } from '@/components/ui/button';
import { StatusBadge } from '@/components/ui/status-badge';
import { useActiveSessionBlock } from '@/hooks/use-active-session-block';
import {
    isActiveGameSessionStatus,
    useSessionSync,
} from '@/hooks/use-session-sync';
import type { GameSession, SearchUser, SharedData } from '@/types';

type ShowSessionProps = {
    session: GameSession;
    showActiveSessionBlock?: boolean;
};

export default function ShowSession({
    session,
    showActiveSessionBlock = false,
}: ShowSessionProps) {
    const { modal } = useActiveSessionBlock({
        initiallyOpen: showActiveSessionBlock,
    });
    const { post, processing } = useForm({});
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const { auth } = usePage<SharedData>().props;

    useSessionSync({
        shouldSync: isActiveGameSessionStatus(session.status),
        only: ['session'],
        paused: processing,
    });

    const addPlayer = (player: SearchUser) => {
        router.post(`/sessions/${session.id}/players`, {
            user_id: player.id,
        });
    };

    return (
        <>
            <Head title={session.name} />
            {modal}
            <MobileLayout
                title={session.name}
                subtitle="Session details and player roster."
            >
                <div className="flex flex-1 flex-col gap-5">
                    <div className="flex items-center justify-between gap-3">
                        <StatusBadge
                            status={session.status}
                            label={session.status_label}
                        />
                        {session.is_room_master ? (
                            <span className="rounded-full bg-amber-400/15 px-3 py-1 text-xs font-semibold text-amber-200 ring-1 ring-amber-300/30">
                                Room master
                            </span>
                        ) : null}
                    </div>

                    <div className="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <dl className="space-y-4 text-sm">
                            <div>
                                <dt className="text-emerald-100/60">
                                    Room master
                                </dt>
                                <dd className="mt-1 font-medium text-white">
                                    {session.room_master.name}{' '}
                                    <span className="text-emerald-100/60">
                                        @{session.room_master.username}
                                    </span>
                                </dd>
                            </div>
                            {session.started_at ? (
                                <div>
                                    <dt className="text-emerald-100/60">
                                        Started
                                    </dt>
                                    <dd className="mt-1 text-white">
                                        {new Date(
                                            session.started_at,
                                        ).toLocaleString()}
                                    </dd>
                                </div>
                            ) : null}
                            {session.finishing_at ? (
                                <div>
                                    <dt className="text-emerald-100/60">
                                        Finishing
                                    </dt>
                                    <dd className="mt-1 text-white">
                                        {new Date(
                                            session.finishing_at,
                                        ).toLocaleString()}
                                    </dd>
                                </div>
                            ) : null}
                            {session.completed_at ? (
                                <div>
                                    <dt className="text-emerald-100/60">
                                        Completed
                                    </dt>
                                    <dd className="mt-1 text-white">
                                        {new Date(
                                            session.completed_at,
                                        ).toLocaleString()}
                                    </dd>
                                </div>
                            ) : null}
                        </dl>
                    </div>

                    <div>
                        <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-emerald-100/60">
                            Players ({session.players.length}/
                            {session.max_players})
                        </h2>
                        <ul className="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                            {session.players.map((player) => (
                                <li
                                    key={player.id}
                                    className="flex min-h-14 items-center justify-between gap-3 border-b border-white/5 px-4 last:border-b-0"
                                >
                                    <div>
                                        <p className="font-medium text-white">
                                            {player.name}
                                        </p>
                                        <p className="text-sm text-emerald-100/60">
                                            @{player.username}
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        {player.is_room_master ? (
                                            <span className="block text-xs font-medium text-amber-200">
                                                Master
                                            </span>
                                        ) : null}
                                        {player.has_submitted_money ? (
                                            <span className="block text-sm font-semibold text-white">
                                                {player.total_money}
                                            </span>
                                        ) : session.status === 'finishing' ? (
                                            <span className="block text-xs text-emerald-100/50">
                                                Pending
                                            </span>
                                        ) : null}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {session.can_add_players ? (
                        <PlayerSearch
                            onAdd={addPlayer}
                            excludePlayerIds={session.players.map(
                                (player) => player.id,
                            )}
                            maxPlayers={session.max_players}
                            currentPlayerCount={session.players.length}
                            disabled={processing}
                            error={errors.user_id}
                        />
                    ) : null}

                    {session.status === 'finishing' ? (
                        <SessionMoneySettlement
                            session={session}
                            error={errors.total_money ?? errors.session}
                        />
                    ) : null}

                    <div className="mt-auto flex flex-col gap-3 pt-4">
                        {session.status === 'waiting' &&
                        auth.user &&
                        !session.viewer_player_id &&
                        !showActiveSessionBlock ? (
                            <Button
                                fullWidth
                                disabled={processing}
                                onClick={() =>
                                    post(`/sessions/${session.id}/join`)
                                }
                            >
                                Join session
                            </Button>
                        ) : null}

                        {session.is_room_master &&
                        session.status === 'waiting' ? (
                            <Button
                                fullWidth
                                disabled={processing}
                                onClick={() =>
                                    post(`/sessions/${session.id}/start`)
                                }
                            >
                                Start game
                            </Button>
                        ) : null}

                        {session.is_room_master &&
                        session.status === 'in_progress' ? (
                            <Button
                                fullWidth
                                variant="secondary"
                                disabled={processing}
                                onClick={() =>
                                    post(`/sessions/${session.id}/finish`)
                                }
                            >
                                Enter finishing
                            </Button>
                        ) : null}

                        {session.is_room_master &&
                        session.status === 'finishing' ? (
                            <Button
                                fullWidth
                                disabled={
                                    processing || !session.can_complete
                                }
                                onClick={() =>
                                    post(`/sessions/${session.id}/complete`)
                                }
                            >
                                Mark as complete
                            </Button>
                        ) : null}

                        {session.is_room_master &&
                        session.status === 'finishing' &&
                        !session.all_money_submitted ? (
                            <p className="text-center text-xs text-emerald-100/60">
                                Waiting for all players to submit their
                                totals.
                            </p>
                        ) : null}

                        <Link
                            href="/user"
                            className="inline-flex min-h-11 items-center justify-center text-sm font-medium text-emerald-200/80"
                        >
                            Back to my table
                        </Link>
                    </div>
                </div>
            </MobileLayout>
        </>
    );
}
