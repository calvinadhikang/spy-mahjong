<?php

namespace App\Http\Controllers;

use App\Enums\GameSessionStatus;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    public function show(): Response
    {
        $user = Auth::user();
        $activeSession = $user?->activeGameSession();

        return Inertia::render('user/dashboard', [
            'activeSession' => $activeSession
                ? $activeSession->toPageArray($user)
                : null,
        ]);
    }

    public function history(): Response
    {
        $user = Auth::user();

        $sessions = $user
            ?->gameSessions()
            ->where('game_sessions.status', GameSessionStatus::Completed)
            ->with(['roomMaster', 'players'])
            ->orderByDesc('game_sessions.completed_at')
            ->get()
            ->map(fn ($session) => $session->toHistoryArray($user))
            ->values()
            ->all() ?? [];

        return Inertia::render('user/history', [
            'sessions' => $sessions,
        ]);
    }
}
