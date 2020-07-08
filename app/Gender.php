<?php

namespace App;

use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Gender extends Model
{
    use SoftDeletes, Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the users for the gender.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
