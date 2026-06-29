import { Link, useForm, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

import { Button } from '@/components/ui/button';
import type { SharedData } from '@/types';

type AdminLayoutProps = {
    children: ReactNode;
    title: string;
    subtitle?: string;
};

const adminLinks = [
    { href: '/admin/users', label: 'Users' },
    { href: '/admin/xp-settings', label: 'XP settings' },
    { href: '/admin/levels', label: 'Levels' },
];

export function AdminLayout({ children, title, subtitle }: AdminLayoutProps) {
    const { url } = usePage<SharedData>();
    const { post, processing } = useForm({});

    return (
        <div className="min-h-dvh bg-gradient-to-b from-emerald-950 via-emerald-900 to-emerald-950 text-white">
            <div className="mx-auto flex min-h-dvh w-full max-w-md flex-col px-5 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
                <header className="pt-6">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <p className="text-xs font-semibold uppercase tracking-wide text-amber-300/80">
                            Admin
                        </p>
                        <Link
                            href="/user"
                            className="text-sm text-emerald-200/70 hover:text-emerald-100"
                        >
                            Back to app
                        </Link>
                    </div>
                    <h1 className="text-3xl font-bold tracking-tight">{title}</h1>
                    {subtitle ? (
                        <p className="mt-2 text-base text-emerald-100/70">
                            {subtitle}
                        </p>
                    ) : null}
                    <nav className="mt-6 flex flex-wrap gap-2">
                        {adminLinks.map((link) => {
                            const isActive = url.startsWith(link.href);

                            return (
                                <Link
                                    key={link.href}
                                    href={link.href}
                                    className={`rounded-xl px-4 py-2 text-sm font-medium transition ${
                                        isActive
                                            ? 'bg-emerald-500 text-white'
                                            : 'bg-white/5 text-emerald-100/70 hover:bg-white/10'
                                    }`}
                                >
                                    {link.label}
                                </Link>
                            );
                        })}
                    </nav>
                    <div className="mt-4">
                        <Button
                            variant="ghost"
                            disabled={processing}
                            onClick={() => post('/admin/logout')}
                        >
                            Sign out
                        </Button>
                    </div>
                </header>
                <main className="flex flex-1 flex-col py-8">{children}</main>
            </div>
        </div>
    );
}
