import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

type TabItem = {
    href: string;
    label: string;
    icon: (active: boolean) => ReactNode;
    match: (url: string) => boolean;
};

function TableIcon({ active }: { active: boolean }) {
    return (
        <svg
            aria-hidden="true"
            viewBox="0 0 24 24"
            fill="none"
            className={cn('size-6', active ? 'text-emerald-300' : 'text-emerald-200/60')}
            stroke="currentColor"
            strokeWidth="1.75"
        >
            <rect x="3" y="5" width="18" height="14" rx="2.5" />
            <path d="M3 10h18" />
            <circle cx="8" cy="14.5" r="1.25" fill="currentColor" stroke="none" />
            <circle cx="12" cy="14.5" r="1.25" fill="currentColor" stroke="none" />
            <circle cx="16" cy="14.5" r="1.25" fill="currentColor" stroke="none" />
        </svg>
    );
}

function PastIcon({ active }: { active: boolean }) {
    return (
        <svg
            aria-hidden="true"
            viewBox="0 0 24 24"
            fill="none"
            className={cn('size-6', active ? 'text-emerald-300' : 'text-emerald-200/60')}
            stroke="currentColor"
            strokeWidth="1.75"
        >
            <path d="M12 8v5l3 2" strokeLinecap="round" strokeLinejoin="round" />
            <circle cx="12" cy="12" r="8.5" />
        </svg>
    );
}

function ProfileIcon({ active }: { active: boolean }) {
    return (
        <svg
            aria-hidden="true"
            viewBox="0 0 24 24"
            fill="none"
            className={cn('size-6', active ? 'text-emerald-300' : 'text-emerald-200/60')}
            stroke="currentColor"
            strokeWidth="1.75"
        >
            <circle cx="12" cy="8.5" r="3.25" />
            <path
                d="M5.5 19.5c1.4-3 4.1-4.5 6.5-4.5s5.1 1.5 6.5 4.5"
                strokeLinecap="round"
            />
        </svg>
    );
}

const tabs: TabItem[] = [
    {
        href: '/user',
        label: 'Table',
        icon: (active) => <TableIcon active={active} />,
        match: (url) =>
            url === '/user' ||
            url.startsWith('/sessions/create') ||
            url.startsWith('/sessions/'),
    },
    {
        href: '/user/history',
        label: 'Past',
        icon: (active) => <PastIcon active={active} />,
        match: (url) => url.startsWith('/user/history'),
    },
    {
        href: '/user/profile',
        label: 'Me',
        icon: (active) => <ProfileIcon active={active} />,
        match: (url) => url.startsWith('/user/profile'),
    },
];

export function MobileBottomTabBar() {
    const { auth } = usePage<SharedData>().props;
    const { url } = usePage();

    if (!auth.user) {
        return null;
    }

    return (
        <nav
            aria-label="Main navigation"
            className="fixed inset-x-0 bottom-0 z-10 border-t border-white/10 bg-emerald-950/95 backdrop-blur-md pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        >
            <div className="mx-auto flex max-w-md items-stretch justify-around px-2 pt-1">
                {tabs.map((tab) => {
                    const active = tab.match(url);

                    return (
                        <Link
                            key={tab.href}
                            href={tab.href}
                            className={cn(
                                'flex min-h-[3.75rem] min-w-16 flex-1 flex-col items-center justify-center gap-1 rounded-xl px-2 py-1 transition active:scale-[0.97]',
                                active ? 'text-white' : 'text-emerald-200/70',
                            )}
                        >
                            {tab.icon(active)}
                            <span
                                className={cn(
                                    'text-[11px] font-semibold tracking-wide',
                                    active ? 'text-emerald-200' : 'text-emerald-100/50',
                                )}
                            >
                                {tab.label}
                            </span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
