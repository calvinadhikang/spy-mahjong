import type { ActiveSessionSummary } from './game-session';

export type User = {
    id: number;
    name: string;
    username: string;
    is_admin: boolean;
    total_xp: number;
    created_at: string;
    updated_at: string;
};

export type Auth = {
    user: User | null;
};

export type SharedData = {
    name: string;
    auth: Auth;
    activeSession: ActiveSessionSummary | null;
};
