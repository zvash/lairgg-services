<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderRequest extends Model
{
    protected $fillable = [
        'user_id',
        'requestable_type',
        'requestable_id',
    ];

    protected $appends = [
        'type',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function requestable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return mixed|string
     */
    public function getTypeAttribute()
    {
        if ($this->requestable_type == Order::class) {
            return 'order';
        }
        if ($this->requestable_type == CashOut::class) {
            return 'cash_out';
        }
        return $this->requestable_type;
    }
}
