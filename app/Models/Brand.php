<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use BelongsToTenant;

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'name', 'description', 'image', 'woocommerce_id',
    ];
}
