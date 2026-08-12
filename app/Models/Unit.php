<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use BelongsToTenant;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'name', 'ShortName', 'base_unit', 'operator', 'operator_value', 'is_active',
    ];

    protected $casts = [
        'base_unit' => 'integer',
        'operator_value' => 'float',
        'is_active' => 'integer',

    ];
}
