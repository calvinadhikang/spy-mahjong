import { router } from '@inertiajs/react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { GameSession } from '@/types';

type SessionMoneySettlementProps = {
    session: GameSession;
    error?: string;
};

export function SessionMoneySettlement({
    session,
    error,
}: SessionMoneySettlementProps) {
    const viewer = session.players.find(
        (player) => player.id === session.viewer_player_id,
    );

    if (!session.can_submit_money || !viewer) {
        return null;
    }

    if (session.is_room_master) {
        return (
            <div className="space-y-4">
                <div>
                    <h2 className="text-sm font-semibold uppercase tracking-wide text-emerald-100/60">
                        Settle totals
                    </h2>
                    <p className="mt-2 text-sm text-emerald-100/70">
                        Enter each player&apos;s total. Mark complete once
                        everyone has a total.
                    </p>
                </div>

                <ul className="space-y-3">
                    {session.players.map((player) => (
                        <PlayerMoneyForm
                            key={player.id}
                            sessionId={session.id}
                            player={player}
                        />
                    ))}
                </ul>

                {error ? <p className="text-sm text-red-300">{error}</p> : null}
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <div>
                <h2 className="text-sm font-semibold uppercase tracking-wide text-emerald-100/60">
                    Your total
                </h2>
                <p className="mt-2 text-sm text-emerald-100/70">
                    Submit your final amount for this session.
                </p>
            </div>

            <PlayerMoneyForm
                sessionId={session.id}
                player={viewer}
                error={error}
            />
        </div>
    );
}

type PlayerMoneyFormProps = {
    sessionId: number;
    player: GameSession['players'][number];
    error?: string;
};

function PlayerMoneyForm({ sessionId, player, error }: PlayerMoneyFormProps) {
    const [totalMoney, setTotalMoney] = useState(
        player.total_money?.toString() ?? '',
    );
    const [processing, setProcessing] = useState(false);

    const submit = () => {
        setProcessing(true);

        router.post(
            `/sessions/${sessionId}/money`,
            {
                user_id: player.id,
                total_money: totalMoney,
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
            <div className="mb-3 flex items-start justify-between gap-3">
                <div>
                    <p className="font-medium text-white">{player.name}</p>
                    <p className="text-sm text-emerald-100/60">
                        @{player.username}
                    </p>
                </div>
                {player.has_submitted_money ? (
                    <span className="rounded-full bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-200">
                        Submitted
                    </span>
                ) : (
                    <span className="rounded-full bg-white/10 px-2.5 py-1 text-xs font-semibold text-emerald-100/60">
                        Pending
                    </span>
                )}
            </div>

            <Input
                name={`total_money_${player.id}`}
                label="Total money"
                type="number"
                inputMode="decimal"
                step="0.01"
                placeholder="0.00"
                value={totalMoney}
                onChange={(event) => setTotalMoney(event.target.value)}
                error={error}
            />

            <Button
                fullWidth
                className="mt-3"
                disabled={processing || totalMoney === ''}
                onClick={submit}
            >
                {processing ? 'Saving…' : 'Save total'}
            </Button>
        </div>
    );
}
