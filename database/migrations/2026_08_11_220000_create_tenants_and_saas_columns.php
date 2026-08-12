<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('status', 20)->default('active'); // active|suspended
                $table->longText('module_flags')->nullable(); // JSON {key: bool}
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
            }
            if (! Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('statut');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
            }
        });

        // Backfill: one default tenant for the existing single-install data.
        $existing = DB::table('tenants')->where('slug', 'main')->first();
        if (! $existing) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Main',
                'slug' => 'main',
                'status' => 'active',
                'module_flags' => null, // null = all modules enabled
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $tenantId = $existing->id;
        }

        // Copy module_flags from settings if present.
        $settings = DB::table('settings')->orderBy('id')->first();
        if ($settings && ! empty($settings->module_flags)) {
            DB::table('tenants')->where('id', $tenantId)->update([
                'module_flags' => $settings->module_flags,
            ]);
        }

        DB::table('settings')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        DB::table('roles')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        DB::table('users')->whereNull('tenant_id')->where('is_super_admin', false)->update(['tenant_id' => $tenantId]);

        // Ensure there is a platform super admin (email unique).
        $superEmail = 'superadmin@example.com';
        $super = DB::table('users')->where('email', $superEmail)->first();
        if (! $super) {
            DB::table('users')->insert([
                'firstname' => 'Super',
                'lastname' => 'Admin',
                'username' => 'Super Admin',
                'email' => $superEmail,
                // password: 123456
                'password' => '$2y$10$IFj6SwqC0Sxrsiv4YkCt.OJv1UV4mZrWuyLoRG7qt47mseP9mJ58u',
                'avatar' => 'no_avatar.png',
                'phone' => '0000000000',
                'role_id' => 1,
                'statut' => 1,
                'is_all_warehouses' => 1,
                'record_view' => 1,
                'tenant_id' => null,
                'is_super_admin' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('id', $super->id)->update([
                'is_super_admin' => 1,
                'tenant_id' => null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenant_id')) {
                $table->dropColumn('tenant_id');
            }
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'tenant_id')) {
                $table->dropColumn('tenant_id');
            }
        });
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'tenant_id')) {
                $table->dropColumn('tenant_id');
            }
        });
        Schema::dropIfExists('tenants');
    }
};
