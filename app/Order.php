<?php

namespace App;

use App\Enums\OrderStatus;
use App\Traits\Eloquents\Transactionable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Order extends Model
{
    use Actionable, Transactionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'integer',
        'redeem_points' => 'integer',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => OrderStatus::PENDING,
        'redeem_points' => 0,
    ];

    /**
     * Get the product that owns the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that owns the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check the order canceled.
     *
     * @return bool
     */
    public function isCanceled()
    {
        return $this->status == OrderStatus::CANCEL;
    }

    /**
     * Check the order doesn't canceled but it was canceled before.
     *
     * @return bool
     */
    public function isCanceledBefore()
    {
        return ! $this->isCanceled() && $this->getOriginal('status') == OrderStatus::CANCEL;
    }
}
