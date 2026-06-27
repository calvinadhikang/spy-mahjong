<?php

namespace App\Models;

use App\Enums\GameSessionStatus;
use App\Enums\IdentityProvider;
use App\Enums\PlayerOutcome;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property bool $is_admin
 * @property int $total_xp
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'username', 'is_admin', 'total_xp'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'total_xp' => 'integer',
        ];
    }

    /**
     * @return HasMany<UserIdentity, $this>
     */
    public function identities(): HasMany
    {
        return $this->hasMany(UserIdentity::class);
    }

    public function passwordIdentity(): ?UserIdentity
    {
        return $this->identities
            ->firstWhere('provider', IdentityProvider::Password);
    }

    /**
     * @return BelongsToMany<GameSession, $this>
     */
    public function gameSessions(): BelongsToMany
    {
        return $this->belongsToMany(GameSession::class, 'game_session_user')
            ->withPivot(['total_money', 'money_submitted_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<GameSessionPlayerResult, $this>
     */
    public function gameSessionResults(): HasMany
    {
        return $this->hasMany(GameSessionPlayerResult::class);
    }

    public function resolveLevel(): ?Level
    {
        return Level::query()
            ->where('min_xp', '<=', (int) ($this->total_xp ?? 0))
            ->orderByDesc('min_xp')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function progressionToPageArray(): array
    {
        $level = $this->resolveLevel();

        return [
            'total_xp' => $this->total_xp ?? 0,
            'level_name' => $level?->name,
            'level_min_xp' => $level?->min_xp,
        ];
    }

    public function activeGameSession(): ?GameSession
    {
        return $this->gameSessions()
            ->whereIn('status', [
                GameSessionStatus::Waiting,
                GameSessionStatus::InProgress,
                GameSessionStatus::Finishing,
            ])
            ->latest('game_sessions.updated_at')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function gameStats(): array
    {
        $sessions = $this->gameSessions()
            ->where('game_sessions.status', GameSessionStatus::Completed)
            ->orderByDesc('game_sessions.completed_at')
            ->get();

        $resultsBySession = GameSessionPlayerResult::query()
            ->where('user_id', $this->id)
            ->whereIn('game_session_id', $sessions->pluck('id'))
            ->get()
            ->keyBy('game_session_id');

        $totalGames = $sessions->count();

        if ($totalGames === 0) {
            return $this->emptyGameStats();
        }

        $wins = 0;
        $losses = 0;
        $breakEven = 0;
        $totalProfit = 0.0;
        $bestGame = null;
        $worstGame = null;
        $gamesAsRoomMaster = 0;
        $gamesAsGuest = 0;
        $roomMasterWins = 0;
        $roomMasterLosses = 0;
        $guestWins = 0;
        $guestLosses = 0;
        $streakEntries = [];

        foreach ($sessions as $session) {
            $result = $resultsBySession->get($session->id);

            if ($result) {
                $money = $result->score !== null ? (float) $result->score : 0.0;
                $outcome = $result->outcome->value;
            } else {
                $money = $session->pivot->total_money !== null
                    ? (float) $session->pivot->total_money
                    : 0.0;
                $outcome = PlayerOutcome::fromMoney($money)->value;
            }

            $streakEntries[] = $outcome;
            $totalProfit += $money;

            if ($outcome === PlayerOutcome::Win->value) {
                $wins++;
            } elseif ($outcome === PlayerOutcome::Loss->value) {
                $losses++;
            } else {
                $breakEven++;
            }

            if ($bestGame === null || $money > $bestGame) {
                $bestGame = $money;
            }

            if ($worstGame === null || $money < $worstGame) {
                $worstGame = $money;
            }

            if ($session->room_master_id === $this->id) {
                $gamesAsRoomMaster++;

                if ($outcome === PlayerOutcome::Win->value) {
                    $roomMasterWins++;
                } elseif ($outcome === PlayerOutcome::Loss->value) {
                    $roomMasterLosses++;
                }
            } else {
                $gamesAsGuest++;

                if ($outcome === PlayerOutcome::Win->value) {
                    $guestWins++;
                } elseif ($outcome === PlayerOutcome::Loss->value) {
                    $guestLosses++;
                }
            }
        }

        $decidedGames = $wins + $losses;

        $currentStreak = null;
        $streakType = null;
        $streakCount = 0;

        foreach ($streakEntries as $outcome) {
            if ($streakType === null) {
                $streakType = $outcome;
                $streakCount = 1;
            } elseif ($outcome === $streakType) {
                $streakCount++;
            } else {
                break;
            }
        }

        if ($streakType !== null) {
            $currentStreak = [
                'type' => $streakType,
                'count' => $streakCount,
            ];
        }

        return [
            'total_games' => $totalGames,
            'wins' => $wins,
            'losses' => $losses,
            'break_even' => $breakEven,
            'win_rate' => round(($wins / $totalGames) * 100, 1),
            'decided_win_rate' => $decidedGames > 0
                ? round(($wins / $decidedGames) * 100, 1)
                : null,
            'total_profit' => round($totalProfit, 2),
            'average_profit' => round($totalProfit / $totalGames, 2),
            'best_game' => $bestGame,
            'worst_game' => $worstGame,
            'games_as_room_master' => $gamesAsRoomMaster,
            'games_as_guest' => $gamesAsGuest,
            'room_master_wins' => $roomMasterWins,
            'room_master_losses' => $roomMasterLosses,
            'guest_wins' => $guestWins,
            'guest_losses' => $guestLosses,
            'current_streak' => $currentStreak,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyGameStats(): array
    {
        return [
            'total_games' => 0,
            'wins' => 0,
            'losses' => 0,
            'break_even' => 0,
            'win_rate' => null,
            'decided_win_rate' => null,
            'total_profit' => 0.0,
            'average_profit' => null,
            'best_game' => null,
            'worst_game' => null,
            'games_as_room_master' => 0,
            'games_as_guest' => 0,
            'room_master_wins' => 0,
            'room_master_losses' => 0,
            'guest_wins' => 0,
            'guest_losses' => 0,
            'current_streak' => null,
        ];
    }
}
