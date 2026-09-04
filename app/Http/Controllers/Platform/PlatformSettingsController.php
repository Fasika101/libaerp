<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Site-wide (platform) settings: the settings row with tenant_id = NULL.
 * It brands the login page and acts as the fallback whenever no tenant
 * context exists. Only the super admin can read/write it.
 */
class PlatformSettingsController extends Controller
{
    /** Whitelisted editable fields. */
    protected const FIELDS = [
        'app_name', 'CompanyName', 'CompanyPhone', 'CompanyAdress', 'email',
        'footer', 'developed_by', 'page_title_suffix', 'default_language',
    ];

    public function show()
    {
        $settings = $this->globalRow();

        $out = [];
        foreach (self::FIELDS as $field) {
            $out[$field] = $settings->{$field} ?? null;
        }
        $out['logo'] = $settings->logo ?? null;
        $out['favicon'] = $settings->favicon ?? null;

        return response()->json(['settings' => $out]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name' => 'nullable|string|max:191',
            'CompanyName' => 'nullable|string|max:191',
            'CompanyPhone' => 'nullable|string|max:191',
            'CompanyAdress' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'footer' => 'nullable|string|max:191',
            'developed_by' => 'nullable|string|max:191',
            'page_title_suffix' => 'nullable|string|max:191',
            'default_language' => 'nullable|string|max:10',
        ]);

        $settings = $this->globalRow();
        foreach (self::FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $settings->{$field} = $data[$field];
            }
        }

        // Logo: same treatment as Appearance Settings (resized copy in /images).
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logo = $request->file('logo');
            $filename = rand(11111111, 99999999).$logo->getClientOriginalName();
            Image::make($logo->getRealPath())->resize(80, 80)->save(public_path('/images/'.$filename));
            $settings->logo = $filename;
        }

        // Favicon: ico/png only, moved as-is.
        if ($request->hasFile('favicon') && $request->file('favicon')->isValid()) {
            $favicon = $request->file('favicon');
            $extension = strtolower($favicon->getClientOriginalExtension());
            if (in_array($extension, ['ico', 'png'])) {
                $filename = uniqid().'.'.$extension;
                $favicon->move(public_path('images'), $filename);
                $settings->favicon = $filename;
            }
        }

        $settings->save();

        return response()->json(['success' => true]);
    }

    protected function globalRow(): Setting
    {
        $row = Setting::withoutGlobalScopes()->whereNull('tenant_id')->first();
        if (! $row) {
            // Should have been seeded by the migration; recover from the
            // oldest tenant row so the endpoint never 500s on a fresh DB.
            $source = Setting::withoutGlobalScopes()->orderBy('id')->first();
            $row = $source ? $source->replicate() : new Setting;
            $row->tenant_id = null;
            $row->save();
        }

        return $row;
    }
}
