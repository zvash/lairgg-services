<?php

namespace App;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Product extends Model
{
    use SoftDeletes, Actionable;

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
        'points' => 'integer',
        'quantity' => 'integer',
        'status' => 'integer',
        'attributes' => 'array',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'points' => 0,
        'quantity' => 0,
        'status' => ProductStatus::ACTIVE,
    ];

    protected $hidden = [
        'quantity'
    ];

    /**
     * Get the orders for the game.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
