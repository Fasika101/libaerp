<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user() ?: Auth::user();
        if (! $user || ! $user->is_super_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
