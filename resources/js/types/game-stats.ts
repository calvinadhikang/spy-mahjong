export type GameStreak = {
    type: 'win' | 'loss' | 'even';
    count: number;
};

export type GameStats = {
    total_games: number;
    wins: number;
    losses: number;
    break_even: number;
    win_rate: number | null;
    decided_win_rate: number | null;
    total_profit: number;
    average_profit: number | null;
    best_game: number | null;
    worst_game: number | null;
    games_as_room_master: number;
    games_as_guest: number;
    room_master_wins: number;
    room_master_losses: number;
    guest_wins: number;
    guest_losses: number;
    current_streak: GameStreak | null;
};
