<?php

namespace App\Models;

use App\Enums\PlayerOutcome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_session_id
 * @property int $user_id
 * @property int $placement
 * @property PlayerOutcome $outcome
 * @property int $xp_earned
 * @property string|null $score
 * @property string $scoring_rule_version
 * @property Carbon $computed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GameSessionPlayerResult extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'game_session_id',
        'user_id',
        'placement',
        'outcome',
        'xp_earned',
        'score',
        'scoring_rule_version',
        'computed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'outcome' => PlayerOutcome::class,
            'computed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<GameSession, $this>
     */
    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
