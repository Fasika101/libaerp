<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTest extends Model
{
    use BelongsToTenant;

    use SoftDeletes;

    protected $table = 'lab_tests';

    protected $fillable = [
        'tenant_id',
        'name', 'code', 'category', 'sample_type', 'unit', 'normal_range',
        'price', 'turnaround_hours', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'turnaround_hours' => 'integer',
        'is_active' => 'boolean',
    ];
}
