import type { ReactNode } from 'react';
import { usePage } from '@inertiajs/react';

import { MobileBottomTabBar } from '@/components/navigation/mobile-bottom-tab-bar';
import { MobileTopBar } from '@/components/navigation/mobile-top-bar';
import type { SharedData } from '@/types';

type MobileLayoutProps = {
    children: ReactNode;
    title?: string;
    subtitle?: string;
};

export function MobileLayout({ children, title, subtitle }: MobileLayoutProps) {
    const { auth } = usePage<SharedData>().props;
    const hasBottomTabs = auth.user !== null;

    return (
        <div className="min-h-dvh bg-gradient-to-b from-emerald-950 via-emerald-900 to-emerald-950 text-white">
            <div
                className={`mx-auto flex min-h-dvh w-full max-w-md flex-col px-5 ${
                    hasBottomTabs
                        ? 'pb-[calc(4.75rem+env(safe-area-inset-bottom))]'
                        : 'pb-[max(1.5rem,env(safe-area-inset-bottom))]'
                }`}
            >
                <MobileTopBar />
                {(title || subtitle) && (
                    <header className="mb-8">
                        {title ? (
                            <h1 className="text-3xl font-bold tracking-tight">
                                {title}
                            </h1>
                        ) : null}
                        {subtitle ? (
                            <p className="mt-2 text-base text-emerald-100/70">
                                {subtitle}
                            </p>
                        ) : null}
                    </header>
                )}
                <main className="flex flex-1 flex-col">{children}</main>
            </div>
            <MobileBottomTabBar />
        </div>
    );
}
