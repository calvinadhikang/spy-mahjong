<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminLoginController extends Controller
{
    private const CONSOLE_PASSWORD = 'spy_admin';

    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->session()->get('admin_console_authenticated')) {
            return redirect()->route('admin.users.index');
        }

        return Inertia::render('admin/login');
    }

    public function store(AdminLoginRequest $request): RedirectResponse
    {
        if ($request->string('password')->value() !== self::CONSOLE_PASSWORD) {
            return back()->withErrors([
                'password' => 'That password is incorrect.',
            ]);
        }

        $request->session()->put('admin_console_authenticated', true);

        return redirect()->route('admin.users.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_console_authenticated');

        return redirect()->route('admin.login');
    }
}
