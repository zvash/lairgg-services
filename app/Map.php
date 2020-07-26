<?php

namespace App;

use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Map extends Model
{
    use SoftDeletes, Actionable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the game that owns the map.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the plays for the map.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function plays()
    {
        return $this->hasMany(Play::class);
    }
}
