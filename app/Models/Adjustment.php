<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
    use BelongsToTenant;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'date', 'Ref', 'user_id', 'warehouse_id', 'time',
        'items', 'notes', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'warehouse_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function details()
    {
        return $this->hasMany('App\Models\AdjustmentDetail');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }
}
