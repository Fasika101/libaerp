<?php

use App\Models\StoreSetting;
use App\Models\Setting;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Cache;

if (! function_exists('store_settings')) {
    function store_settings(): StoreSetting
    {
        return Cache::remember('store_settings', 600, function () {
            return StoreSetting::query()->first() ?? new StoreSetting;
        });
    }
}

if (! function_exists('tenant_settings')) {
    /**
     * Settings row for the current tenant (or explicit tenant id).
     */
    function tenant_settings(?int $tenantId = null): ?Setting
    {
        $id = $tenantId ?? TenantContext::id();
        if (! $id) {
            return Setting::withoutGlobalScopes()->orderBy('id')->first();
        }

        return Setting::withoutGlobalScopes()->where('tenant_id', $id)->first();
    }
}
