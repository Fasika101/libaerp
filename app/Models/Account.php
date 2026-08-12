<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'account_num', 'account_name', 'initial_balance', 'balance', 'note', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'initial_balance' => 'double',
        'balance' => 'double',
    ];
}
