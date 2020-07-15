<?php

namespace App;

use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class PrizeType extends Model
{
    use Actionable, SoftDeletes;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the prizes for the prize type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prizes()
    {
        return $this->hasMany(Prize::class);
    }
}
