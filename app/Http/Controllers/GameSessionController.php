<?php

namespace App\Http\Controllers;

use App\Enums\GameSessionStatus;
use App\Http\Requests\GameSessions\AddPlayerRequest;
use App\Http\Requests\GameSessions\StoreGameSessionRequest;
use App\Http\Requests\GameSessions\SubmitPlayerMoneyRequest;
use App\Models\GameSession;
use App\Models\User;
use App\Services\MatchResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GameSessionController extends Controller
{
    public function create(Request $request): Response
    {
        $user = Auth::user();

        if (! $user?->is_admin) {
            abort(403);
        }

        $hasActiveSession = $user->activeGameSession() !== null;

        return Inertia::render('sessions/create', [
            'showActiveSessionBlock' => $hasActiveSession,
        ]);
    }

    public function store(StoreGameSessionRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return back()->withErrors([
                'name' => 'Please log in to create a session.',
            ]);
        }

        if (! $user->is_admin) {
            abort(403);
        }

        if ($user->activeGameSession()) {
            return redirect()
                ->route('game-sessions.create')
                ->with('active_session_block', true);
        }

        $session = DB::transaction(function () use ($request, $user): GameSession {
            $session = GameSession::query()->create([
                'name' => $request->string('name')->value(),
                'room_master_id' => $user->id,
                'status' => GameSessionStatus::Waiting,
            ]);

            return $session;
        });

        return redirect()->route('game-sessions.show', $session);
    }

    public function destroy(GameSession $gameSession): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $gameSession->isRoomMaster($user)) {
            abort(403);
        }

        if ($gameSession->status !== GameSessionStatus::Waiting) {
            return back()->withErrors([
                'session' => 'Only waiting sessions can be deleted.',
            ]);
        }

        $gameSession->delete();

        return redirect()->route('user.dashboard');
    }

    public function show(GameSession $gameSession): Response
    {
        $user = Auth::user();
        $activeSession = $user?->activeGameSession();
        $isJoiningAnotherRoom = $activeSession !== null
            && $activeSession->id !== $gameSession->id;

        return Inertia::render('sessions/show', [
            'session' => $gameSession->toPageArray($user),
            'showActiveSessionBlock' => $isJoiningAnotherRoom,
        ]);
    }

    public function addPlayer(AddPlayerRequest $request, GameSession $gameSession): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $gameSession->isRoomMaster($user)) {
            abort(403);
        }

        if ($gameSession->status !== GameSessionStatus::Waiting) {
            return back()->withErrors([
                'user_id' => 'Players can only be added before the game starts.',
            ]);
        }

        if ($gameSession->players()->count() >= GameSession::MAX_PLAYERS) {
            return back()->withErrors([
                'user_id' => 'This session already has the maximum of '.GameSession::MAX_PLAYERS.' players.',
            ]);
        }

        $player = User::query()->findOrFail($request->integer('user_id'));

        if ($gameSession->hasPlayer($player)) {
            return back()->withErrors([
                'user_id' => 'That player is already in this session.',
            ]);
        }

        $playerActiveSession = $player->activeGameSession();

        if ($playerActiveSession && $playerActiveSession->id !== $gameSession->id) {
            return back()->withErrors([
                'user_id' => "{$player->username} must finish their current game before joining another room.",
            ]);
        }

        $gameSession->players()->attach($player->id);

        return redirect()->route('game-sessions.show', $gameSession);
    }

    public function start(GameSession $gameSession): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $gameSession->isRoomMaster($user)) {
            abort(403);
        }

        if ($gameSession->status !== GameSessionStatus::Waiting) {
            return back()->withErrors([
                'session' => 'Only waiting sessions can be started.',
            ]);
        }

        $gameSession->update([
            'status' => GameSessionStatus::InProgress,
            'started_at' => now(),
        ]);

        return redirect()->route('game-sessions.show', $gameSession);
    }

    public function finish(GameSession $gameSession): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $gameSession->isRoomMaster($user)) {
            abort(403);
        }

        if ($gameSession->status !== GameSessionStatus::InProgress) {
            return back()->withErrors([
                'session' => 'Only in-progress sessions can enter finishing.',
            ]);
        }

        $gameSession->update([
            'status' => GameSessionStatus::Finishing,
            'finishing_at' => now(),
        ]);

        return redirect()->route('game-sessions.show', $gameSession);
    }

    public function submitMoney(SubmitPlayerMoneyRequest $request, GameSession $gameSession): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $gameSession->canSubmitMoney($user)) {
            abort(403);
        }

        $targetUserId = $request->has('user_id')
            ? $request->integer('user_id')
            : $user->id;

        if (! $gameSession->isRoomMaster($user) && $targetUserId !== $user->id) {
            abort(403);
        }

        $targetUser = User::query()->findOrFail($targetUserId);

        if (! $gameSession->hasPlayer($targetUser)) {
            return back()->withErrors([
                'total_money' => 'That player is not in this session.',
            ]);
        }

        $gameSession->players()->updateExistingPivot($targetUserId, [
            'total_money' => $request->input('total_money'),
            'money_submitted_at' => now(),
        ]);

        return redirect()->route('game-sessions.show', $gameSession);
    }

    public function complete(GameSession $gameSession): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $gameSession->canComplete($user)) {
            if ($user && $gameSession->isRoomMaster($user) && $gameSession->status === GameSessionStatus::Finishing) {
                return back()->withErrors([
                    'session' => 'All players must submit their totals before completing the session.',
                ]);
            }

            abort(403);
        }

        DB::transaction(function () use ($gameSession): void {
            $gameSession->update([
                'status' => GameSessionStatus::Completed,
                'completed_at' => now(),
            ]);

            if (! $gameSession->playerResults()->exists()) {
                app(MatchResultService::class)->process($gameSession);
            }
        });

        return redirect()->route('game-sessions.show', $gameSession);
    }

    public function join(GameSession $gameSession): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if ($gameSession->hasPlayer($user)) {
            return redirect()->route('game-sessions.show', $gameSession);
        }

        if ($gameSession->status !== GameSessionStatus::Waiting) {
            return back()->withErrors([
                'session' => 'You can only join sessions that are waiting to start.',
            ]);
        }

        if ($gameSession->players()->count() >= GameSession::MAX_PLAYERS) {
            return back()->withErrors([
                'session' => 'This session is already full.',
            ]);
        }

        $activeSession = $user->activeGameSession();

        if ($activeSession && $activeSession->id !== $gameSession->id) {
            return redirect()
                ->route('user.dashboard')
                ->with('active_session_block', true);
        }

        $gameSession->players()->attach($user->id);

        return redirect()->route('game-sessions.show', $gameSession);
    }

    public function leave(GameSession $gameSession): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $gameSession->canLeave($user)) {
            abort(403);
        }

        $gameSession->players()->detach($user->id);

        if ($gameSession->isRoomMaster($user)) {
            return redirect()->route('game-sessions.show', $gameSession);
        }

        return redirect()->route('user.dashboard');
    }

    public function removePlayer(GameSession $gameSession, User $user): RedirectResponse
    {
        $viewer = Auth::user();

        if (! $viewer || ! $gameSession->canRemovePlayer($viewer, $user)) {
            abort(403);
        }

        $gameSession->players()->detach($user->id);

        return redirect()->route('game-sessions.show', $gameSession);
    }
}
