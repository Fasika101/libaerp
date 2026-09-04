<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SaaS phase 2:
 * - users.acting_tenant_id lets a super admin "enter" a company; the tenant
 *   middleware then scopes the whole request to that company.
 * - A settings row with tenant_id = NULL becomes the platform-level (site-wide)
 *   settings, editable only by the super admin and used as the fallback when
 *   no tenant context exists (login page, super admin outside a company).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'acting_tenant_id')) {
                $table->unsignedBigInteger('acting_tenant_id')->nullable()->after('tenant_id')->index();
            }
        });

        // Seed the global settings row from the oldest tenant row so the site
        // starts with sensible branding instead of blanks.
        $hasGlobal = DB::table('settings')->whereNull('tenant_id')->exists();
        if (! $hasGlobal) {
            $source = DB::table('settings')->orderBy('id')->first();
            if ($source) {
                $row = (array) $source;
                unset($row['id']);
                $row['tenant_id'] = null;
                $row['created_at'] = now();
                $row['updated_at'] = now();
                DB::table('settings')->insert($row);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'acting_tenant_id')) {
                $table->dropColumn('acting_tenant_id');
            }
        });
        DB::table('settings')->whereNull('tenant_id')->delete();
    }
};
