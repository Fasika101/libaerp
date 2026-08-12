<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'patients',
        'doctors',
        'appointments',
        'patient_visits',
        'admissions',
        'lab_tests',
        'lab_orders',
        'lab_order_items',
        'marketing_activity_logs',
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
