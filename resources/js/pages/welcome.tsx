import { Head, Link, usePage } from '@inertiajs/react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import type { SharedData } from '@/types';

export default function Welcome() {
    const { auth, name } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Home" />
            <MobileLayout>
                <div className="flex flex-1 flex-col">
                    <div className="flex flex-1 flex-col items-center justify-center text-center">
                        <div className="mb-6 flex size-20 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-4xl shadow-xl shadow-emerald-950/40">
                            🀄
                        </div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300/80">
                            {name}
                        </p>
                        <h1 className="mt-3 text-4xl font-bold tracking-tight">
                            Spy Mahjong
                        </h1>
                        <p className="mt-4 max-w-xs text-base leading-relaxed text-emerald-100/70">
                            Bluff, deduce, and outplay your friends in a social
                            mahjong mystery.
                        </p>

                        {auth.user ? (
                            <p className="mt-8 text-sm text-emerald-100/70">
                                Welcome back,{' '}
                                <span className="font-semibold text-white">
                                    {auth.user.username}
                                </span>
                            </p>
                        ) : null}
                    </div>

                    {!auth.user ? (
                        <div className="flex flex-col gap-3 pb-2">
                            <Link
                                href="/login"
                                className="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-emerald-500 px-5 text-base font-semibold text-white shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-400 active:scale-[0.98]"
                            >
                                Log in
                            </Link>
                            <Link
                                href="/register"
                                className="inline-flex min-h-12 w-full items-center justify-center rounded-xl border border-white/15 bg-white/10 px-5 text-base font-semibold text-white transition hover:bg-white/15 active:scale-[0.98]"
                            >
                                Create account
                            </Link>
                        </div>
                    ) : null}
                </div>
            </MobileLayout>
        </>
    );
}
