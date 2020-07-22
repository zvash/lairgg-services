<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Order extends Model
{
    use Actionable;

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
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 0,
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
}
