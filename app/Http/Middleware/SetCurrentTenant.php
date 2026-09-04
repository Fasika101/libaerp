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
        } elseif ($user && $user->is_super_admin && $user->acting_tenant_id && ! $request->is('api/platform*')) {
            // A super admin who "entered" a company works inside its scope,
            // even when the tenant is suspended (they still need to manage it).
            // Platform routes stay global so the companies list is never scoped.
            $tenant = Tenant::query()->find($user->acting_tenant_id);
            if ($tenant) {
                TenantContext::set($tenant);
            }
        }

        return $next($request);
    }
}
