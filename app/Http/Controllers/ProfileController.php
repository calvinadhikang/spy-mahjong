<?php

namespace App\Http\Controllers;

use App\Enums\IdentityProvider;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();

        return Inertia::render('user/profile', [
            'profile' => [
                'name' => $user?->name,
                'username' => $user?->username,
                'can_update_password' => $user?->identities()
                    ->where('provider', IdentityProvider::Password)
                    ->exists() ?? false,
            ],
            'stats' => $user?->gameStats(),
            'progression' => $user?->progressionToPageArray(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'name' => $request->string('name')->value(),
            'username' => $request->string('username')->value(),
        ]);

        if ($request->filled('password')) {
            $user->passwordIdentity()?->update([
                'secret' => $request->string('password')->value(),
            ]);
        }

        return redirect()
            ->route('user.profile')
            ->with('profile_updated', true);
    }
}
