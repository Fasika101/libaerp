<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair: product_warehouse rows were bulk-inserted (Model::insert bypasses
 * the BelongsToTenant creating hook) so tenant_id stayed NULL and the rows
 * were invisible to tenant-scoped queries — freshly created products never
 * showed up in the purchase/sale product pickers. Inherit the tenant from
 * the parent product.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_warehouse', 'tenant_id')) {
            return;
        }

        // Pre-SaaS / demo products that never got a tenant belong to the
        // default "Main" tenant (same rule as the original backfill).
        $defaultTenantId = DB::table('tenants')->where('slug', 'main')->value('id')
            ?? DB::table('tenants')->orderBy('id')->value('id');
        if ($defaultTenantId) {
            DB::table('products')->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }

        DB::statement(
            'UPDATE product_warehouse pw
             JOIN products p ON p.id = pw.product_id
             SET pw.tenant_id = p.tenant_id
             WHERE pw.tenant_id IS NULL AND p.tenant_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        // Data repair — nothing sensible to revert.
    }
};
