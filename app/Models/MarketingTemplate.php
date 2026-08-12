<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingTemplate extends Model
{
    use BelongsToTenant;

    use SoftDeletes;

    protected $table = 'marketing_templates';

    protected $fillable = [
        'tenant_id',
        'name', 'type', 'category', 'subject', 'content', 'created_by',
    ];

    public function campaigns()
    {
        return $this->hasMany(MarketingCampaign::class, 'template_id');
    }
}
