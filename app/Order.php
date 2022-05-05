<?php

namespace App;

use App\Enums\OrderStatus;
use App\Repositories\CountryRepository;
use App\Traits\Eloquents\Requestable;
use App\Traits\Eloquents\Transactionable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Order extends Model
{
    use Actionable, Transactionable, Requestable;

    protected $fillable = [
        'product_id',
        'user_id',
        'redeem_points',
        'name',
        'address',
        'state',
        'city',
        'country',
        'postal_code',
        'status',
        'is_final',
    ];

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

    protected $hidden = [
        'is_final'
    ];

    protected $appends = [
        'country_name',
        'product_summary',
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

    /**
     * @return bool
     */
    public function isShipped()
    {
        return $this->status == OrderStatus::SHIPPED;
    }

    /**
     * @return string|null
     */
    public function getCountryNameAttribute()
    {
        if (! $this->country) {
            return null;
        }
        $repository = new CountryRepository();
        return $repository->getName($this->country);
    }

    /**
     * @return array
     */
    public function getProductSummaryAttribute()
    {
        $product = $this->product;
        $image = $product->images()->first();
        if ($image) {
            $image = $image->image;
        }
        return [
            'title' => $product->title,
            'image' => $image,
        ];
    }
}
