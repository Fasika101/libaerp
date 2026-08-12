<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use BelongsToTenant;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'user_id', 'date', 'Ref', 'sale_id', 'delivered_to', 'shipping_address', 'status', 'shipping_details',

    ];

    protected $casts = [
        'user_id' => 'integer',
        'sale_id' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
