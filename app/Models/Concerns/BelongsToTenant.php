<?php

namespace App\Models\Concerns;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Scopes queries to the current tenant and stamps tenant_id on create.
 * Super-admin / no-tenant context: scope is not applied (platform code must
 * filter explicitly or use withoutGlobalScopes).
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->tenant_id) && TenantContext::id()) {
                $model->tenant_id = TenantContext::id();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = TenantContext::id();
            if ($tenantId) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
