<?php

use App\Http\Controllers\Admin\AdminLevelController;
use App\Http\Controllers\Admin\AdminXpSettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserSearchController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/user', [UserDashboardController::class, 'show'])->name('user.dashboard');
Route::get('/user/history', [UserDashboardController::class, 'history'])->name('user.history');
Route::get('/user/profile', [ProfileController::class, 'edit'])->name('user.profile');
Route::put('/user/profile', [ProfileController::class, 'update'])->name('user.profile.update');

Route::get('/users/search', [UserSearchController::class, 'index'])->name('users.search');

Route::get('/sessions/create', [GameSessionController::class, 'create'])->name('game-sessions.create');
Route::post('/sessions', [GameSessionController::class, 'store'])->name('game-sessions.store');
Route::get('/sessions/{gameSession}', [GameSessionController::class, 'show'])->name('game-sessions.show');
Route::post('/sessions/{gameSession}/join', [GameSessionController::class, 'join'])->name('game-sessions.join');
Route::post('/sessions/{gameSession}/players', [GameSessionController::class, 'addPlayer'])->name('game-sessions.players.store');
Route::post('/sessions/{gameSession}/start', [GameSessionController::class, 'start'])->name('game-sessions.start');
Route::post('/sessions/{gameSession}/finish', [GameSessionController::class, 'finish'])->name('game-sessions.finish');
Route::post('/sessions/{gameSession}/money', [GameSessionController::class, 'submitMoney'])->name('game-sessions.money.store');
Route::post('/sessions/{gameSession}/complete', [GameSessionController::class, 'complete'])->name('game-sessions.complete');

Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/xp-settings')->name('home');

        Route::get('/xp-settings', [AdminXpSettingsController::class, 'edit'])
            ->name('xp-settings.edit');
        Route::put('/xp-settings', [AdminXpSettingsController::class, 'update'])
            ->name('xp-settings.update');

        Route::get('/levels', [AdminLevelController::class, 'index'])
            ->name('levels.index');
        Route::post('/levels', [AdminLevelController::class, 'store'])
            ->name('levels.store');
        Route::put('/levels/{level}', [AdminLevelController::class, 'update'])
            ->name('levels.update');
        Route::delete('/levels/{level}', [AdminLevelController::class, 'destroy'])
            ->name('levels.destroy');
    });
