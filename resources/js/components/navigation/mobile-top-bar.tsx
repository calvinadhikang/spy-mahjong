import { Link, usePage } from '@inertiajs/react';

import type { SharedData } from '@/types';

const guestLinks = [
    { href: '/login', label: 'Log in' },
    { href: '/register', label: 'Register' },
] as const;

export function MobileTopBar() {
    const { auth, activeSession } = usePage<SharedData>().props;

    return (
        <header className="sticky top-0 z-10 -mx-5 mb-6 border-b border-white/10 bg-emerald-950/90 px-5 pb-3 pt-[max(0.25rem,env(safe-area-inset-top))] backdrop-blur-md">
            <div className="flex min-h-11 items-center justify-between gap-3">
                <Link
                    href="/"
                    className="flex min-h-11 min-w-0 items-center gap-2 rounded-lg active:bg-white/5"
                    aria-label="Spy Mahjong home"
                >
                    <span className="flex size-9 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-lg">
                        🀄
                    </span>
                    <span className="hidden truncate text-sm font-bold tracking-tight sm:inline">
                        Spy Mahjong
                    </span>
                </Link>

                {auth.user ? (
                    activeSession ? (
                        <Link
                            href={`/sessions/${activeSession.id}`}
                            className="inline-flex max-w-[55%] min-h-9 items-center gap-2 truncate rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-semibold text-emerald-200 ring-1 ring-emerald-300/25 active:bg-emerald-400/25"
                        >
                            <span className="size-2 shrink-0 rounded-full bg-emerald-400" />
                            <span className="truncate">{activeSession.name}</span>
                        </Link>
                    ) : null
                ) : (
                    <nav className="flex shrink-0 items-center gap-1">
                        {guestLinks.map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className="inline-flex min-h-10 items-center rounded-lg px-3 text-sm font-medium text-emerald-200/80 transition hover:bg-white/5 hover:text-white active:scale-[0.98]"
                            >
                                {link.label}
                            </Link>
                        ))}
                    </nav>
                )}
            </div>
        </header>
    );
}
