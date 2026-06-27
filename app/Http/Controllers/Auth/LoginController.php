<?php

namespace App\Http\Controllers\Auth;

use App\Enums\IdentityProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $user = User::query()
            ->where('username', $request->string('username')->lower()->value())
            ->with('identities')
            ->first();

        $identity = $user?->identities
            ->firstWhere('provider', IdentityProvider::Password);

        if (! $user || ! $identity || ! Hash::check($request->string('password')->value(), $identity->secret)) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'These credentials do not match our records.',
                ]);
        }

        $identity->update(['last_used_at' => now()]);

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('user.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
