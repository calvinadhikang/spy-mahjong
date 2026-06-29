<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->orderBy('username')
            ->get(['id', 'username', 'is_admin'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'username' => $user->username,
                'is_admin' => $user->is_admin,
            ])
            ->values()
            ->all();

        return Inertia::render('admin/users', [
            'users' => $users,
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('admin_user_updated', true);
    }
}
