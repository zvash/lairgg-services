<?php

namespace App;

use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class LinkType extends Model
{
    use SoftDeletes, Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the links for the link type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function links()
    {
        return $this->hasMany(Link::class);
    }
}
