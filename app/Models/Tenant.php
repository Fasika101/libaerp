<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'module_flags',
    ];

    protected $casts = [
        'module_flags' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** True when the given module key is enabled for this tenant. */
    public function moduleEnabled(string $key): bool
    {
        $flags = $this->module_flags;
        if (! is_array($flags)) {
            return true;
        }

        return ($flags[$key] ?? true) !== false;
    }
}
