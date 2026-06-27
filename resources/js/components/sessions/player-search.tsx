import { useEffect, useState } from 'react';

import type { SearchUser } from '@/types';

type PlayerSearchProps = {
    onAdd: (player: SearchUser) => void;
    excludePlayerIds: number[];
    maxPlayers: number;
    currentPlayerCount: number;
    disabled?: boolean;
    error?: string;
};

export function PlayerSearch({
    onAdd,
    excludePlayerIds,
    maxPlayers,
    currentPlayerCount,
    disabled = false,
    error,
}: PlayerSearchProps) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<SearchUser[]>([]);
    const [isSearching, setIsSearching] = useState(false);

    const slotsRemaining = maxPlayers - currentPlayerCount;
    const isFull = slotsRemaining <= 0;

    useEffect(() => {
        const trimmed = query.trim();

        if (trimmed.length < 2 || disabled || isFull) {
            setResults([]);
            return;
        }

        const timeout = window.setTimeout(async () => {
            setIsSearching(true);

            try {
                const response = await fetch(
                    `/users/search?q=${encodeURIComponent(trimmed)}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    },
                );

                if (!response.ok) {
                    return;
                }

                const data = (await response.json()) as {
                    users: SearchUser[];
                };

                const excludedIds = new Set(excludePlayerIds);

                setResults(
                    data.users.filter((user) => !excludedIds.has(user.id)),
                );
            } finally {
                setIsSearching(false);
            }
        }, 300);

        return () => window.clearTimeout(timeout);
    }, [query, excludePlayerIds, disabled, isFull]);

    return (
        <div className="space-y-3">
            <div className="space-y-2">
                <label
                    htmlFor="player-search"
                    className="block text-sm font-medium text-emerald-100/90"
                >
                    Add players
                </label>
                <input
                    id="player-search"
                    type="search"
                    value={query}
                    disabled={disabled || isFull}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder="Search by name or username"
                    className="min-h-12 w-full rounded-xl border border-white/10 bg-white/5 px-4 text-base text-white placeholder:text-white/35 outline-none transition focus:border-emerald-400/60 focus:ring-2 focus:ring-emerald-400/20 disabled:cursor-not-allowed disabled:opacity-50"
                />
                <p className="text-xs text-emerald-100/50">
                    {isFull
                        ? `Table is full (${maxPlayers}/${maxPlayers} players).`
                        : `${currentPlayerCount}/${maxPlayers} players · ${slotsRemaining} slot${slotsRemaining === 1 ? '' : 's'} left`}
                </p>
            </div>

            {isSearching ? (
                <p className="text-sm text-emerald-100/60">Searching…</p>
            ) : null}

            {results.length > 0 ? (
                <ul className="overflow-hidden rounded-xl border border-white/10 bg-white/5">
                    {results.map((user) => (
                        <li key={user.id}>
                            <button
                                type="button"
                                disabled={disabled || isFull}
                                onClick={() => {
                                    onAdd(user);
                                    setQuery('');
                                    setResults([]);
                                }}
                                className="flex min-h-12 w-full items-center justify-between gap-3 px-4 text-left transition hover:bg-white/5 active:bg-white/10 disabled:opacity-50"
                            >
                                <span>
                                    <span className="block font-medium text-white">
                                        {user.name}
                                    </span>
                                    <span className="block text-sm text-emerald-100/60">
                                        @{user.username}
                                    </span>
                                </span>
                                <span className="text-sm font-medium text-emerald-300">
                                    Add
                                </span>
                            </button>
                        </li>
                    ))}
                </ul>
            ) : null}

            {error ? <p className="text-sm text-red-300">{error}</p> : null}
        </div>
    );
}
