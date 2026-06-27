<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLevelRequest;
use App\Http\Requests\Admin\UpdateLevelRequest;
use App\Models\Level;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminLevelController extends Controller
{
    public function index(): Response
    {
        $levels = Level::query()
            ->orderBy('min_xp')
            ->get()
            ->map(fn (Level $level) => $level->toPageArray())
            ->values()
            ->all();

        return Inertia::render('admin/levels', [
            'levels' => $levels,
        ]);
    }

    public function store(StoreLevelRequest $request): RedirectResponse
    {
        $maxSortOrder = Level::query()->max('sort_order') ?? 0;

        Level::query()->create([
            'name' => $request->string('name')->value(),
            'min_xp' => $request->integer('min_xp'),
            'sort_order' => $maxSortOrder + 1,
        ]);

        return redirect()
            ->route('admin.levels.index')
            ->with('level_saved', true);
    }

    public function update(UpdateLevelRequest $request, Level $level): RedirectResponse
    {
        $level->update([
            'name' => $request->string('name')->value(),
            'min_xp' => $request->integer('min_xp'),
        ]);

        return redirect()
            ->route('admin.levels.index')
            ->with('level_saved', true);
    }

    public function destroy(Level $level): RedirectResponse
    {
        $level->delete();

        return redirect()
            ->route('admin.levels.index')
            ->with('level_deleted', true);
    }
}
