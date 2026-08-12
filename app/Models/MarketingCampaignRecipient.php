<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaignRecipient extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_campaign_recipients';

    protected $fillable = [
        'tenant_id',
        'campaign_id', 'client_id', 'name', 'phone', 'email', 'status',
        'error_message', 'sent_at', 'delivered_at',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
