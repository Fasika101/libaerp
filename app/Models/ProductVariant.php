<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use BelongsToTenant;

    protected $table = 'product_variants';

    protected $fillable = [
        'tenant_id',
        'product_id', 'name', 'qty', 'cost', 'price', 'wholesale', 'min_price', 'code', 'gtin', 'image',
        'woocommerce_variation_id',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'qty' => 'double',
        'cost' => 'double',
        'price' => 'double',
        'wholesale' => 'double',
        'min_price' => 'double',
        'woocommerce_variation_id' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
