import type { Auth } from '@/types/auth';
import type { ActiveSessionSummary } from '@/types/game-session';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            activeSession: ActiveSessionSummary | null;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
