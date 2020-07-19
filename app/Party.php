<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Party extends Model
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
        'score' => 'integer',
        'is_host' => 'boolean',
        'is_winner' => 'boolean',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'score' => null,
        'is_host' => false,
        'is_winner' => false,
    ];

    /**
     * Get the team that owns the party.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the play that owns the party.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function play()
    {
        return $this->belongsTo(Play::class);
    }
}
