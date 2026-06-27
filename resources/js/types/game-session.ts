export type GameSessionPlayer = {
    id: number;
    name: string;
    username: string;
    is_room_master: boolean;
    total_money: number | null;
    has_submitted_money: boolean;
};

export type GameSession = {
    id: number;
    name: string;
    status: 'waiting' | 'in_progress' | 'finishing' | 'completed';
    status_label: string;
    started_at: string | null;
    finishing_at: string | null;
    completed_at: string | null;
    is_room_master: boolean;
    can_add_players: boolean;
    can_submit_money: boolean;
    can_complete: boolean;
    all_money_submitted: boolean;
    max_players: number;
    viewer_player_id: number | null;
    room_master: GameSessionPlayer;
    players: GameSessionPlayer[];
};

export type PastGameSession = {
    id: number;
    name: string;
    completed_at: string | null;
    player_count: number;
    is_room_master: boolean;
    room_master_name: string;
    viewer_total_money: number | null;
};
export type ActiveSessionSummary = {
    id: number;
    name: string;
    status: GameSession['status'];
    status_label: string;
};

export type SearchUser = {
    id: number;
    name: string;
    username: string;
};
