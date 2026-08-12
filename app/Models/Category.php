<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use BelongsToTenant;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'icon',
    ];

    public function subcategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withTimestamps();
    }
}
