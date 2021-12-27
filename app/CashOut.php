<?php

namespace App;

use App\Traits\Eloquents\Requestable;
use App\Traits\Eloquents\Transactionable;
use Illuminate\Database\Eloquent\Model;

class CashOut extends Model
{
    use Transactionable, Requestable;

    protected $fillable = [
        'user_id',
        'cash_amount',
        'redeem_points',
        'paypal_email',
        'status',
        'is_final',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
