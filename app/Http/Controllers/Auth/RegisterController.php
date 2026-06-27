<?php

namespace App\Http\Controllers\Auth;

use App\Enums\IdentityProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::query()->create([
                'name' => $request->string('name')->value(),
                'username' => $request->string('username')->value(),
            ]);

            $user->identities()->create([
                'provider' => IdentityProvider::Password,
                'secret' => $request->string('password')->value(),
                'last_used_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('user.dashboard');
    }
}
