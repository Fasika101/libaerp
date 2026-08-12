<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $user = Auth::guard('api')->user() ?: Auth::user();
        if ($user && $user->is_super_admin) {
            return $next($request);
        }

        $tenant = TenantContext::get();
        if (! $tenant || ! $tenant->moduleEnabled($module)) {
            return response()->json(['message' => 'Module disabled'], 403);
        }

        return $next($request);
    }
}
