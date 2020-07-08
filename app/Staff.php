<?php

namespace App;

use Illuminate\Database\Eloquent\{
    Builder,
    Model
};
use Laravel\Nova\Actions\Actionable;

class Staff extends Model
{
    use Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'owner' => 'boolean',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'owner' => false,
    ];

    /**
     * Get the organization that owns the staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user that owns the staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the staff type that owns the staff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staffType()
    {
        return $this->belongsTo(StaffType::class);
    }

    /**
     * Get staff base on their owner field.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  bool  $owner
     * @return mixed
     */
    public function scopeOwner(Builder $builder, bool $owner = true)
    {
        return $builder->whereOwner($owner);
    }
}
