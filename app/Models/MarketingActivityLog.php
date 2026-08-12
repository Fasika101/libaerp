<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class MarketingActivityLog extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_activity_logs';

    protected $fillable = [
        'tenant_id',
        'user_id', 'module', 'reference_id', 'action', 'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
