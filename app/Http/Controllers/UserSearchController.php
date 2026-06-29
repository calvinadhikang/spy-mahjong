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
            ->where('username', 'like', '%'.strtolower($query).'%')
            ->orderBy('username')
            ->limit(10)
            ->get(['id', 'username']);

        return response()->json([
            'users' => $users,
        ]);
    }
}
