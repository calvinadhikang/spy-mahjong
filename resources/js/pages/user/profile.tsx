import { Head, Link, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useEffect } from 'react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { GameStats, SharedData, UserProgression } from '@/types';

type ProfileData = {
    name: string | null | undefined;
    username: string | null | undefined;
    can_update_password: boolean;
};

type ProfileProps = {
    profile: ProfileData;
    stats: GameStats | null;
    progression: UserProgression | null;
};

export default function Profile({ profile, stats, progression }: ProfileProps) {
    const { auth, flash } = usePage<
        SharedData & { flash?: { profile_updated?: boolean } }
    >().props;

    const { data, setData, put, processing, errors, recentlySuccessful } =
        useForm({
            name: profile.name ?? '',
            username: profile.username ?? '',
            current_password: '',
            password: '',
            password_confirmation: '',
        });

    useEffect(() => {
        if (recentlySuccessful) {
            setData('current_password', '');
            setData('password', '');
            setData('password_confirmation', '');
        }
    }, [recentlySuccessful, setData]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        put('/user/profile');
    };

    return (
        <>
            <Head title="Profile" />
            <MobileLayout
                title="Profile"
                subtitle={
                    auth.user
                        ? 'Your stats and account details.'
                        : 'Log in to manage your profile.'
                }
            >
                {!auth.user ? (
                    <div className="flex flex-1 flex-col justify-center gap-3">
                        <p className="text-center text-sm text-emerald-100/70">
                            Sign in to view and edit your account.
                        </p>
                        <Link
                            href="/login"
                            className="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-base font-semibold text-white shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-400 active:scale-[0.98]"
                        >
                            Log in
                        </Link>
                    </div>
                ) : (
                    <div className="flex flex-1 flex-col gap-6">
                        {progression ? (
                            <ProfileProgression progression={progression} />
                        ) : null}
                        {stats ? <ProfileStats stats={stats} /> : null}

                        {auth.user?.is_admin ? (
                            <Link
                                href="/admin/xp-settings"
                                className="inline-flex min-h-11 items-center justify-center rounded-xl border border-amber-400/30 bg-amber-400/10 px-4 text-sm font-medium text-amber-200 transition hover:bg-amber-400/20"
                            >
                                Admin settings
                            </Link>
                        ) : null}

                        <form onSubmit={submit} className="flex flex-col gap-5">
                            {(recentlySuccessful ||
                                flash?.profile_updated) && (
                                <p className="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
                                    Profile updated successfully.
                                </p>
                            )}

                            <div>
                                <h2 className="text-sm font-semibold uppercase tracking-wide text-emerald-100/60">
                                    Account
                                </h2>
                            </div>

                            <Input
                                name="name"
                                label="Name"
                                autoComplete="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                error={errors.name}
                            />

                            <Input
                                name="username"
                                label="Username"
                                autoComplete="username"
                                autoCapitalize="none"
                                autoCorrect="off"
                                spellCheck={false}
                                value={data.username}
                                onChange={(event) =>
                                    setData(
                                        'username',
                                        event.target.value.toLowerCase(),
                                    )
                                }
                                error={errors.username}
                            />

                            {profile.can_update_password ? (
                                <div className="space-y-5 rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div>
                                        <h2 className="text-sm font-semibold text-white">
                                            Change password
                                        </h2>
                                        <p className="mt-1 text-xs text-emerald-100/60">
                                            Leave blank to keep your current
                                            password.
                                        </p>
                                    </div>

                                    <Input
                                        name="current_password"
                                        type="password"
                                        label="Current password"
                                        autoComplete="current-password"
                                        value={data.current_password}
                                        onChange={(event) =>
                                            setData(
                                                'current_password',
                                                event.target.value,
                                            )
                                        }
                                        error={errors.current_password}
                                    />

                                    <Input
                                        name="password"
                                        type="password"
                                        label="New password"
                                        autoComplete="new-password"
                                        value={data.password}
                                        onChange={(event) =>
                                            setData(
                                                'password',
                                                event.target.value,
                                            )
                                        }
                                        error={errors.password}
                                    />

                                    <Input
                                        name="password_confirmation"
                                        type="password"
                                        label="Confirm new password"
                                        autoComplete="new-password"
                                        value={data.password_confirmation}
                                        onChange={(event) =>
                                            setData(
                                                'password_confirmation',
                                                event.target.value,
                                            )
                                        }
                                        error={errors.password_confirmation}
                                    />
                                </div>
                            ) : null}

                            <div className="mt-auto flex flex-col gap-3 pt-4">
                                <Button
                                    type="submit"
                                    fullWidth
                                    disabled={processing}
                                >
                                    {processing ? 'Saving…' : 'Save changes'}
                                </Button>

                                <SignOutButton />
                            </div>
                        </form>
                    </div>
                )}
            </MobileLayout>
        </>
    );
}

function ProfileProgression({
    progression,
}: {
    progression: UserProgression;
}) {
    return (
        <section className="rounded-2xl border border-white/10 bg-white/5 p-5">
            <h2 className="text-sm font-semibold uppercase tracking-wide text-emerald-100/60">
                Progression
            </h2>
            <div className="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <p className="text-xs text-emerald-100/60">Total XP</p>
                    <p className="mt-1 text-2xl font-bold text-emerald-300">
                        {progression.total_xp}
                    </p>
                </div>
                <div>
                    <p className="text-xs text-emerald-100/60">Level</p>
                    <p className="mt-1 text-2xl font-bold text-white">
                        {progression.level_name ?? 'Unranked'}
                    </p>
                    {progression.level_min_xp != null ? (
                        <p className="mt-1 text-xs text-emerald-100/50">
                            From {progression.level_min_xp} XP
                        </p>
                    ) : null}
                </div>
            </div>
        </section>
    );
}

function ProfileStats({ stats }: { stats: GameStats }) {
    if (stats.total_games === 0) {
        return (
            <section className="rounded-2xl border border-dashed border-white/15 bg-white/5 p-5 text-center">
                <p className="text-3xl">📊</p>
                <p className="mt-3 text-sm font-medium text-white">
                    No game stats yet
                </p>
                <p className="mt-1 text-xs text-emerald-100/60">
                    Complete your first session to start tracking wins, losses,
                    and profit.
                </p>
            </section>
        );
    }

    return (
        <section className="space-y-4">
            <div>
                <h2 className="text-sm font-semibold uppercase tracking-wide text-emerald-100/60">
                    Game stats
                </h2>
                <p className="mt-1 text-xs text-emerald-100/50">
                    Based on {stats.total_games} completed{' '}
                    {stats.total_games === 1 ? 'game' : 'games'}
                </p>
            </div>

            <div className="grid grid-cols-2 gap-3">
                <StatCard
                    label="Win rate"
                    value={formatPercent(stats.win_rate)}
                    highlight="emerald"
                />
                <StatCard
                    label="Total games"
                    value={stats.total_games.toString()}
                />
                <StatCard
                    label="Wins"
                    value={stats.wins.toString()}
                    highlight="emerald"
                />
                <StatCard
                    label="Losses"
                    value={stats.losses.toString()}
                    highlight="red"
                />
            </div>

            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                <h3 className="text-xs font-semibold uppercase tracking-wide text-emerald-100/60">
                    Record
                </h3>
                <dl className="mt-3 space-y-2">
                    <StatRow
                        label="Decided win rate"
                        value={formatPercent(stats.decided_win_rate)}
                        hint="Excludes break-even games"
                    />
                    <StatRow
                        label="Break even"
                        value={stats.break_even.toString()}
                    />
                    <StatRow
                        label="Current streak"
                        value={formatStreak(stats.current_streak)}
                    />
                </dl>
            </div>

            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                <h3 className="text-xs font-semibold uppercase tracking-wide text-emerald-100/60">
                    Money
                </h3>
                <dl className="mt-3 space-y-2">
                    <StatRow
                        label="Total profit"
                        value={formatMoney(stats.total_profit)}
                        valueClassName={moneyClassName(stats.total_profit)}
                    />
                    <StatRow
                        label="Average per game"
                        value={formatMoney(stats.average_profit)}
                        valueClassName={moneyClassName(stats.average_profit)}
                    />
                    <StatRow
                        label="Best game"
                        value={formatMoney(stats.best_game)}
                        valueClassName="text-emerald-300"
                    />
                    <StatRow
                        label="Worst game"
                        value={formatMoney(stats.worst_game)}
                        valueClassName="text-red-300"
                    />
                </dl>
            </div>

            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                <h3 className="text-xs font-semibold uppercase tracking-wide text-emerald-100/60">
                    By role
                </h3>
                <dl className="mt-3 space-y-2">
                    <StatRow
                        label="As room master"
                        value={`${stats.games_as_room_master} games · ${stats.room_master_wins}W ${stats.room_master_losses}L`}
                    />
                    <StatRow
                        label="As guest"
                        value={`${stats.games_as_guest} games · ${stats.guest_wins}W ${stats.guest_losses}L`}
                    />
                </dl>
            </div>
        </section>
    );
}

function StatCard({
    label,
    value,
    highlight,
}: {
    label: string;
    value: string;
    highlight?: 'emerald' | 'red';
}) {
    const valueClassName =
        highlight === 'emerald'
            ? 'text-emerald-300'
            : highlight === 'red'
              ? 'text-red-300'
              : 'text-white';

    return (
        <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
            <p className="text-xs text-emerald-100/60">{label}</p>
            <p className={`mt-1 text-2xl font-bold ${valueClassName}`}>
                {value}
            </p>
        </div>
    );
}

function StatRow({
    label,
    value,
    hint,
    valueClassName = 'text-white',
}: {
    label: string;
    value: string;
    hint?: string;
    valueClassName?: string;
}) {
    return (
        <div className="flex items-start justify-between gap-3 text-sm">
            <dt className="text-emerald-100/70">
                {label}
                {hint ? (
                    <span className="mt-0.5 block text-xs text-emerald-100/40">
                        {hint}
                    </span>
                ) : null}
            </dt>
            <dd className={`shrink-0 font-semibold ${valueClassName}`}>
                {value}
            </dd>
        </div>
    );
}

function formatPercent(value: number | null): string {
    if (value === null) {
        return '—';
    }

    return `${value}%`;
}

function formatMoney(value: number | null): string {
    if (value === null) {
        return '—';
    }

    const prefix = value > 0 ? '+' : '';

    return `${prefix}${value}`;
}

function moneyClassName(value: number | null): string {
    if (value === null || value === 0) {
        return 'text-white';
    }

    return value > 0 ? 'text-emerald-300' : 'text-red-300';
}

function formatStreak(streak: GameStats['current_streak']): string {
    if (!streak) {
        return '—';
    }

    const label =
        streak.type === 'win'
            ? 'Win'
            : streak.type === 'loss'
              ? 'Loss'
              : 'Even';

    return `${streak.count} ${label}${streak.count === 1 ? '' : 's'}`;
}

function SignOutButton() {
    const { post, processing } = useForm({});

    return (
        <Button
            type="button"
            fullWidth
            variant="secondary"
            disabled={processing}
            onClick={() => post('/logout')}
        >
            {processing ? 'Signing out…' : 'Sign out'}
        </Button>
    );
}
