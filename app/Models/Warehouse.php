<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use BelongsToTenant;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'name', 'mobile', 'country', 'city', 'email', 'zip',
    ];

    public function assignedUsers()
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class);
    }
}
