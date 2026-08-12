<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class AdjustmentDetail extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'id', 'product_id', 'adjustment_id', 'quantity', 'type', 'product_variant_id',
    ];

    protected $casts = [
        'adjustment_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'double',
        'product_variant_id' => 'integer',
    ];

    public function adjustment()
    {
        return $this->belongsTo('App\Models\Adjustment');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
