<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class ValueType extends Model
{
    use Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the prizes for the value type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prizes()
    {
        return $this->hasMany(Prize::class);
    }
}
