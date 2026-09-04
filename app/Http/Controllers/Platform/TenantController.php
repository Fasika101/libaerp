<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    /** Catalog of assignable SaaS modules (matches resources/src/config/modules.js + stock). */
    public static function moduleCatalog(): array
    {
        return [
            'stock', 'Store', 'hrm', 'recruit', 'meeting', 'marketing', 'accounting',
            'EWallet', 'commissions', 'promotions', 'woocommerce_settings', 'shopify',
            'documents', 'subscription_product', 'manufacturing', 'assets', 'projects',
            'bookings', 'service', 'fleet', 'hospital', 'school',
        ];
    }

    public function index()
    {
        $tenants = Tenant::query()->orderByDesc('id')->get()->map(function (Tenant $t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'status' => $t->status,
                'module_flags' => $t->module_flags,
                'users_count' => $t->users()->count(),
                'created_at' => optional($t->created_at)->toDateTimeString(),
            ];
        });

        return response()->json([
            'tenants' => $tenants,
            'module_catalog' => self::moduleCatalog(),
        ]);
    }

    public function show($id)
    {
        $tenant = Tenant::query()->findOrFail($id);
        $admins = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_super_admin', false)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'firstname', 'lastname', 'email', 'username', 'statut']);

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status,
                'module_flags' => $tenant->module_flags,
            ],
            'admins' => $admins,
            'module_catalog' => self::moduleCatalog(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191|unique:tenants,slug',
            'status' => 'nullable|in:active,suspended',
            'module_flags' => 'nullable|array',
            'admin_firstname' => 'required|string|max:191',
            'admin_lastname' => 'required|string|max:191',
            'admin_email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'admin_password' => 'required|string|min:6',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);
        if (Tenant::where('slug', $slug)->exists()) {
            $slug .= '-'.Str::random(4);
        }

        $flags = $this->normalizeFlags($data['module_flags'] ?? null);

        $result = DB::transaction(function () use ($data, $slug, $flags) {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $slug,
                'status' => $data['status'] ?? 'active',
                'module_flags' => $flags,
            ]);

            $this->provisionTenant($tenant, $data);

            return $tenant;
        });

        return response()->json([
            'success' => true,
            'tenant' => $result,
            'message' => 'Tenant created',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::query()->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:191',
            'slug' => 'sometimes|required|string|max:191|unique:tenants,slug,'.$tenant->id,
            'status' => 'sometimes|in:active,suspended',
            'module_flags' => 'nullable|array',
        ]);

        if (array_key_exists('module_flags', $data)) {
            $data['module_flags'] = $this->normalizeFlags($data['module_flags']);
        }

        $tenant->fill($data);
        $tenant->save();

        return response()->json(['success' => true, 'tenant' => $tenant]);
    }

    public function destroy($id)
    {
        $tenant = Tenant::query()->findOrFail($id);
        if ($tenant->slug === 'main') {
            return response()->json(['message' => 'Cannot delete the default Main tenant'], 422);
        }
        $tenant->status = 'suspended';
        $tenant->save();
        $tenant->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Super admin "enters" a company: every subsequent request is scoped to it
     * (SetCurrentTenant reads users.acting_tenant_id), so all regular pages —
     * settings included — operate on that company's data.
     */
    public function switchInto(Request $request, $id)
    {
        $tenant = Tenant::query()->findOrFail($id);
        $user = $request->user('api');
        $user->acting_tenant_id = $tenant->id;
        $user->save();

        return response()->json([
            'success' => true,
            'tenant' => ['id' => $tenant->id, 'name' => $tenant->name, 'slug' => $tenant->slug],
        ]);
    }

    /** Leave the company and return to the global platform view. */
    public function exitTenant(Request $request)
    {
        $user = $request->user('api');
        $user->acting_tenant_id = null;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function createAdmin(Request $request, $id)
    {
        $tenant = Tenant::query()->findOrFail($id);
        $data = $request->validate([
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => 'required|string|min:6',
        ]);

        $role = Role::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'Owner')
            ->first();

        if (! $role) {
            $role = $this->seedOwnerRole($tenant);
        }

        try {
            $user = DB::transaction(function () use ($data, $role, $tenant) {
                $user = User::withoutGlobalScopes()->create([
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'username' => trim($data['firstname'].' '.$data['lastname']),
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'avatar' => 'no_avatar.png',
                    'phone' => '-',
                    'role_id' => $role->id,
                    'statut' => 1,
                    'is_all_warehouses' => 1,
                    'record_view' => 1,
                    'tenant_id' => $tenant->id,
                    'is_super_admin' => false,
                ]);

                \App\Models\role_user::create([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ]);

                return $user;
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Could not create admin: '.$e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true, 'user' => $user], 201);
    }

    public function updateAdmin(Request $request, $id, $userId)
    {
        $tenant = Tenant::query()->findOrFail($id);
        $user = $this->tenantAdmin($tenant, $userId);

        $data = $request->validate([
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6',
            'statut' => ['nullable', Rule::in([0, 1, '0', '1'])],
        ]);

        $willBeActive = array_key_exists('statut', $data) ? (int) $data['statut'] === 1 : (int) $user->statut === 1;
        if (! $willBeActive && $this->isLastActiveAdmin($tenant, $user)) {
            return response()->json([
                'message' => 'A company needs at least one active admin.',
            ], 422);
        }

        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->username = trim($data['firstname'].' '.$data['lastname']);
        $user->email = $data['email'];
        if (array_key_exists('statut', $data)) {
            $user->statut = (int) $data['statut'];
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function destroyAdmin(Request $request, $id, $userId)
    {
        $tenant = Tenant::query()->findOrFail($id);
        $user = $this->tenantAdmin($tenant, $userId);

        if ($this->isLastActiveAdmin($tenant, $user)) {
            return response()->json([
                'message' => 'A company needs at least one active admin.',
            ], 422);
        }

        $user->deleted_at = now();
        $user->statut = 0;
        $user->save();

        return response()->json(['success' => true]);
    }

    protected function tenantAdmin(Tenant $tenant, $userId): User
    {
        return User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_super_admin', false)
            ->whereNull('deleted_at')
            ->findOrFail($userId);
    }

    protected function isLastActiveAdmin(Tenant $tenant, User $user): bool
    {
        if ((int) $user->statut !== 1) {
            return false;
        }

        $active = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_super_admin', false)
            ->whereNull('deleted_at')
            ->where('statut', 1)
            ->count();

        return $active <= 1;
    }

    protected function normalizeFlags(?array $flags): ?array
    {
        if ($flags === null) {
            return null;
        }
        $out = [];
        foreach (self::moduleCatalog() as $key) {
            $out[$key] = array_key_exists($key, $flags) ? (bool) $flags[$key] : false;
        }

        return $out;
    }

    protected function provisionTenant(Tenant $tenant, array $data): void
    {
        Setting::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'email' => $data['admin_email'],
            'currency_id' => 1,
            'client_id' => null,
            'sms_gateway' => 1,
            'point_to_amount_rate' => 1,
            'is_invoice_footer' => 0,
            'CompanyName' => $tenant->name,
            'CompanyPhone' => '',
            'CompanyAdress' => '',
            'footer' => '© '.date('Y').' '.$tenant->name,
            'developed_by' => 'Stocky',
            'logo' => 'logo-default.png',
            'app_name' => $tenant->name,
            'page_title_suffix' => $tenant->name,
            'favicon' => 'favicon.ico',
            'default_language' => 'en',
            'quotation_with_stock' => 1,
            'show_language' => 1,
            'default_tax' => 0,
            'module_flags' => null,
        ]);

        // Default warehouse for the tenant (Phase 2 will rely on tenant_id).
        if (\Illuminate\Support\Facades\Schema::hasColumn('warehouses', 'tenant_id')) {
            Warehouse::withoutGlobalScopes()->create([
                'name' => 'Main Warehouse',
                'city' => '',
                'mobile' => '',
                'zip' => '',
                'email' => $data['admin_email'],
                'country' => '',
                'tenant_id' => $tenant->id,
            ]);
        }

        $role = $this->seedOwnerRole($tenant);

        $user = User::create([
            'firstname' => $data['admin_firstname'],
            'lastname' => $data['admin_lastname'],
            'username' => trim($data['admin_firstname'].' '.$data['admin_lastname']),
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'avatar' => 'no_avatar.png',
            'phone' => '',
            'role_id' => $role->id,
            'statut' => 1,
            'is_all_warehouses' => 1,
            'record_view' => 1,
            'tenant_id' => $tenant->id,
            'is_super_admin' => false,
        ]);
        $user->roles()->sync([$role->id]);
    }

    protected function seedOwnerRole(Tenant $tenant): Role
    {
        $role = Role::withoutGlobalScopes()->create([
            'name' => 'Owner',
            'label' => 'Owner',
            'status' => 1,
            'description' => 'Tenant owner',
            'tenant_id' => $tenant->id,
        ]);
        $permissionIds = Permission::pluck('id')->toArray();
        if ($permissionIds) {
            $role->permissions()->sync($permissionIds);
        }

        return $role;
    }
}
