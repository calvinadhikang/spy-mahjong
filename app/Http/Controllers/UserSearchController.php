<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim($request->string('q')->value());

        if (strlen($query) < 2) {
            return response()->json([
                'users' => [],
            ]);
        }

        $users = User::query()
            ->when(
                $request->user(),
                fn ($builder) => $builder->whereKeyNot($request->user()->id),
            )
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', '%'.strtolower($query).'%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'username']);

        return response()->json([
            'users' => $users,
        ]);
    }
}
