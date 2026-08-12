<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetCurrentTenant
{
    public function handle(Request $request, Closure $next)
    {
        TenantContext::clear();

        $user = Auth::guard('api')->user() ?: Auth::user();
        if ($user && ! $user->is_super_admin && $user->tenant_id) {
            $tenant = Tenant::query()->find($user->tenant_id);
            if ($tenant && $tenant->isActive()) {
                TenantContext::set($tenant);
            } elseif ($tenant && ! $tenant->isActive()) {
                return response()->json(['message' => 'Tenant suspended'], 403);
            }
        }

        return $next($request);
    }
}
