<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingSegment extends Model
{
    use BelongsToTenant;

    use SoftDeletes;

    protected $table = 'marketing_segments';

    protected $fillable = [
        'tenant_id',
        'name', 'description', 'all_customers', 'filters', 'customers_count', 'created_by',
    ];

    protected $casts = [
        'all_customers' => 'boolean',
        'filters'       => 'array',
    ];

    public function campaigns()
    {
        return $this->hasMany(MarketingCampaign::class, 'segment_id');
    }
}
