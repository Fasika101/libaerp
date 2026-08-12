<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        // Hospital
        'hospital_departments',
        'hospital_doctors',
        'hospital_patients',
        'hospital_appointments',
        'hospital_visits',
        'hospital_wards',
        'hospital_beds',
        'hospital_admissions',
        'hospital_lab_tests',
        'hospital_lab_orders',
        'hospital_lab_order_items',
        'hospital_invoices',
        'hospital_invoice_items',
        'hospital_payments',
        // Marketing
        'marketing_campaigns',
        'marketing_segments',
        'marketing_templates',
        'marketing_settings',
        'marketing_messages',
        'marketing_campaign_recipients',
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
