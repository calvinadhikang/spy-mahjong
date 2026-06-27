export type XpRewardSettings = {
    first_place_xp: number;
    second_place_xp: number;
    third_place_xp: number;
    fourth_place_xp: number;
    loss_xp: number;
};

export type Level = {
    id: number;
    name: string;
    min_xp: number;
    sort_order: number;
};

export type UserProgression = {
    total_xp: number;
    level_name: string | null | undefined;
    level_min_xp: number | null | undefined;
};
