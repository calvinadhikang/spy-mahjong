<?php

namespace App\Models;

use App\Enums\GameSessionStatus;
use Database\Factories\GameSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $room_master_id
 * @property GameSessionStatus $status
 * @property Carbon|null $started_at
 * @property Carbon|null $finishing_at
 * @property Carbon|null $completed_at
 * @property string|null $scoring_rule_version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'room_master_id', 'status', 'started_at', 'finishing_at', 'completed_at', 'scoring_rule_version'])]
class GameSession extends Model
{
    public const MAX_PLAYERS = 4;

    /** @use HasFactory<GameSessionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GameSessionStatus::class,
            'started_at' => 'datetime',
            'finishing_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function roomMaster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'room_master_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_session_user')
            ->withPivot(['total_money', 'money_submitted_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<GameSessionPlayerResult, $this>
     */
    public function playerResults(): HasMany
    {
        return $this->hasMany(GameSessionPlayerResult::class);
    }

    public function isRoomMaster(User $user): bool
    {
        return $this->room_master_id === $user->id;
    }

    public function hasPlayer(User $user): bool
    {
        return $this->players()->whereKey($user->id)->exists();
    }

    public function canAddPlayers(?User $viewer): bool
    {
        return $viewer
            && $this->isRoomMaster($viewer)
            && $this->status === GameSessionStatus::Waiting
            && $this->players()->count() < self::MAX_PLAYERS;
    }

    public function allPlayersSubmittedMoney(): bool
    {
        $this->loadMissing('players');

        return $this->players->every(
            fn (User $player) => $player->pivot->money_submitted_at !== null,
        );
    }

    public function canComplete(?User $viewer): bool
    {
        return $viewer
            && $this->isRoomMaster($viewer)
            && $this->status === GameSessionStatus::Finishing
            && $this->allPlayersSubmittedMoney();
    }

    public function canSubmitMoney(?User $viewer): bool
    {
        return $viewer
            && $this->status === GameSessionStatus::Finishing
            && $this->hasPlayer($viewer);
    }

    public function canJoin(?User $viewer): bool
    {
        return $viewer
            && $this->status === GameSessionStatus::Waiting
            && ! $this->hasPlayer($viewer);
    }

    public function canLeave(?User $viewer): bool
    {
        return $viewer
            && $this->status === GameSessionStatus::Waiting
            && $this->hasPlayer($viewer);
    }

    public function canRemovePlayer(?User $viewer, User $player): bool
    {
        return $viewer
            && $this->isRoomMaster($viewer)
            && $this->status === GameSessionStatus::Waiting
            && $this->hasPlayer($player)
            && $player->id !== $viewer->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPageArray(?User $viewer): array
    {
        $this->loadMissing(['roomMaster', 'players']);

        $roomMasterPlayer = $this->players->firstWhere('id', $this->room_master_id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'started_at' => $this->started_at?->toIso8601String(),
            'finishing_at' => $this->finishing_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'is_room_master' => $viewer ? $this->isRoomMaster($viewer) : false,
            'can_add_players' => $this->canAddPlayers($viewer),
            'can_submit_money' => $this->canSubmitMoney($viewer),
            'can_complete' => $this->canComplete($viewer),
            'can_join' => $this->canJoin($viewer),
            'can_leave' => $this->canLeave($viewer),
            'all_money_submitted' => $this->allPlayersSubmittedMoney(),
            'max_players' => self::MAX_PLAYERS,
            'viewer_player_id' => $viewer && $this->hasPlayer($viewer) ? $viewer->id : null,
            'room_master' => [
                'id' => $this->roomMaster->id,
                'username' => $this->roomMaster->username,
                'is_room_master' => true,
                'total_money' => $roomMasterPlayer && $roomMasterPlayer->pivot->total_money !== null
                    ? (float) $roomMasterPlayer->pivot->total_money
                    : null,
                'has_submitted_money' => $roomMasterPlayer?->pivot->money_submitted_at !== null,
            ],
            'players' => $this->players->map(fn (User $player) => [
                'id' => $player->id,
                'username' => $player->username,
                'is_room_master' => $player->id === $this->room_master_id,
                'can_remove' => $this->canRemovePlayer($viewer, $player),
                'total_money' => $player->pivot->total_money !== null
                    ? (float) $player->pivot->total_money
                    : null,
                'has_submitted_money' => $player->pivot->money_submitted_at !== null,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toActiveSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toHistoryArray(?User $viewer): array
    {
        $this->loadMissing(['roomMaster', 'players']);

        $viewerPlayer = $viewer
            ? $this->players->firstWhere('id', $viewer->id)
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'player_count' => $this->players->count(),
            'is_room_master' => $viewer ? $this->isRoomMaster($viewer) : false,
            'room_master_username' => $this->roomMaster->username,
            'viewer_total_money' => $viewerPlayer && $viewerPlayer->pivot->total_money !== null
                ? (float) $viewerPlayer->pivot->total_money
                : null,
        ];
    }
}
