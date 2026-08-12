<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPurchase extends Model
{
    use BelongsToTenant;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'tenant_id',
        'purchase_id', 'date', 'montant', 'change', 'Ref', 'payment_method_id', 'user_id', 'notes', 'account_id',
    ];

    protected $casts = [
        'montant' => 'double',
        'change' => 'double',
        'purchase_id' => 'integer',
        'user_id' => 'integer',
        'account_id' => 'integer',
        'payment_method_id' => 'integer',
    ];

    public function payment_method()
    {
        return $this->belongsTo('App\Models\PaymentMethod');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase');
    }
}
