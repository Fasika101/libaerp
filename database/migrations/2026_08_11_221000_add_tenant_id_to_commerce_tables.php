<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'warehouses',
        'products',
        'product_warehouse',
        'product_variants',
        'brands',
        'categories',
        'units',
        'clients',
        'providers',
        'sales',
        'sale_details',
        'purchases',
        'purchase_details',
        'quotations',
        'quotation_details',
        'adjustments',
        'adjustment_details',
        'transfers',
        'transfer_details',
        'payment_sales',
        'payment_purchases',
        'payment_sale_returns',
        'payment_purchase_returns',
        'sale_returns',
        'sale_return_details',
        'purchase_returns',
        'purchase_return_details',
        'accounts',
        'draft_sales',
        'draft_sale_details',
        'shipments',
    ];

    public function up(): void
    {
        $tenantId = DB::table('tenants')->where('slug', 'main')->value('id')
            ?: DB::table('tenants')->orderBy('id')->value('id');

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->unsignedBigInteger('tenant_id')->nullable()->index();
                });
            }
            if ($tenantId) {
                DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('tenant_id');
                });
            }
        }
    }
};
