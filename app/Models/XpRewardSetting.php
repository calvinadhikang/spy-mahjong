<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $first_place_xp
 * @property int $second_place_xp
 * @property int $third_place_xp
 * @property int $fourth_place_xp
 * @property int $loss_xp
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class XpRewardSetting extends Model
{
    public const DEFAULT_FIRST_PLACE_XP = 100;

    public const DEFAULT_SECOND_PLACE_XP = 60;

    public const DEFAULT_THIRD_PLACE_XP = 30;

    public const DEFAULT_FOURTH_PLACE_XP = 10;

    public const DEFAULT_LOSS_XP = 0;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'first_place_xp',
        'second_place_xp',
        'third_place_xp',
        'fourth_place_xp',
        'loss_xp',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'first_place_xp' => self::DEFAULT_FIRST_PLACE_XP,
            'second_place_xp' => self::DEFAULT_SECOND_PLACE_XP,
            'third_place_xp' => self::DEFAULT_THIRD_PLACE_XP,
            'fourth_place_xp' => self::DEFAULT_FOURTH_PLACE_XP,
            'loss_xp' => self::DEFAULT_LOSS_XP,
        ]);
    }

    public function xpForPlacement(int $placement): int
    {
        return match ($placement) {
            1 => $this->first_place_xp,
            2 => $this->second_place_xp,
            3 => $this->third_place_xp,
            4 => $this->fourth_place_xp,
            default => 0,
        };
    }

    /**
     * @return array<string, int>
     */
    public function toPageArray(): array
    {
        return [
            'first_place_xp' => $this->first_place_xp,
            'second_place_xp' => $this->second_place_xp,
            'third_place_xp' => $this->third_place_xp,
            'fourth_place_xp' => $this->fourth_place_xp,
            'loss_xp' => $this->loss_xp,
        ];
    }
}
