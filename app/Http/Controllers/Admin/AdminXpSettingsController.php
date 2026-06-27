<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateXpRewardSettingsRequest;
use App\Models\XpRewardSetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminXpSettingsController extends Controller
{
    public function edit(): Response
    {
        $settings = XpRewardSetting::current();

        return Inertia::render('admin/xp-settings', [
            'settings' => $settings->toPageArray(),
        ]);
    }

    public function update(UpdateXpRewardSettingsRequest $request): RedirectResponse
    {
        $settings = XpRewardSetting::current();

        $settings->update($request->validated());

        return redirect()
            ->route('admin.xp-settings.edit')
            ->with('xp_settings_updated', true);
    }
}
