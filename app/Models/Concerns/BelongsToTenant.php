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

    /**
     * Bulk insert with tenant stamping. Model::insert() normally forwards
     * straight to the query builder, skipping model events — so the creating()
     * hook above never runs and rows land with tenant_id NULL, invisible to
     * every tenant-scoped query (e.g. products missing from purchase/sale
     * pickers). Defining insert() here takes precedence over the forwarding.
     *
     * @param  array  $values  one row (assoc array) or a list of rows
     */
    public static function insert(array $values): bool
    {
        $tenantId = TenantContext::id();
        if ($tenantId && $values) {
            if (array_is_list($values)) {
                foreach ($values as &$row) {
                    if (is_array($row) && empty($row['tenant_id'])) {
                        $row['tenant_id'] = $tenantId;
                    }
                }
                unset($row);
            } elseif (empty($values['tenant_id'])) {
                $values['tenant_id'] = $tenantId;
            }
        }

        return static::query()->toBase()->insert($values);
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
