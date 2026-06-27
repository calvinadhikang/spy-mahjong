<?php

namespace App\Services;

use App\Enums\PlayerOutcome;
use App\Models\GameSession;
use App\Models\GameSessionPlayerResult;
use App\Models\User;
use App\Models\XpRewardSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchResultService
{
    public const SCORING_RULE_VERSION = 'rank_xp_v1';

    public function process(GameSession $session): void
    {
        $session->loadMissing('players');

        $settings = XpRewardSetting::current();
        $rankedPlayers = $this->rankPlayers($session->players);
        $now = now();

        DB::transaction(function () use ($session, $settings, $rankedPlayers, $now): void {
            foreach ($rankedPlayers as $index => $player) {
                $placement = $index + 1;
                $money = $player->pivot->total_money !== null
                    ? (float) $player->pivot->total_money
                    : 0.0;
                $outcome = PlayerOutcome::fromMoney($money);
                $xpEarned = $this->calculateXp($settings, $placement, $outcome);

                GameSessionPlayerResult::query()->updateOrCreate(
                    [
                        'game_session_id' => $session->id,
                        'user_id' => $player->id,
                    ],
                    [
                        'placement' => $placement,
                        'outcome' => $outcome,
                        'xp_earned' => $xpEarned,
                        'score' => $player->pivot->total_money,
                        'scoring_rule_version' => self::SCORING_RULE_VERSION,
                        'computed_at' => $now,
                    ],
                );

                User::query()
                    ->whereKey($player->id)
                    ->increment('total_xp', $xpEarned);
            }

            $session->update([
                'scoring_rule_version' => self::SCORING_RULE_VERSION,
            ]);
        });
    }

    /**
     * @param  Collection<int, User>  $players
     * @return Collection<int, User>
     */
    private function rankPlayers(Collection $players): Collection
    {
        return $players
            ->sortByDesc(fn (User $player) => $player->pivot->total_money ?? PHP_FLOAT_MIN)
            ->values();
    }

    private function calculateXp(
        XpRewardSetting $settings,
        int $placement,
        PlayerOutcome $outcome,
    ): int {
        if ($outcome === PlayerOutcome::Loss) {
            return $settings->loss_xp;
        }

        if ($outcome === PlayerOutcome::Even) {
            return 0;
        }

        return $settings->xpForPlacement($placement);
    }
}
