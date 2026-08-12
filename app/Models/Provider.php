<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use BelongsToTenant;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'name', 'code', 'adresse', 'phone', 'country', 'email', 'city', 'tax_number',
        'opening_balance', 'credit_limit',
    ];

    protected $casts = [
        'code' => 'integer',
        'opening_balance' => 'double',
        'credit_limit' => 'double',
    ];

    /**
     * Get custom field values for this provider
     */
    public function customFieldValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'entity', 'entity_type', 'entity_id');
    }
}
