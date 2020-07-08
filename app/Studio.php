<?php

namespace App;

use App\Traits\Eloquents\Linkable;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Studio extends Model
{
    use SoftDeletes, Actionable, Linkable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Get the games for the studio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function games()
    {
        return $this->hasMany(Game::class);
    }
}
