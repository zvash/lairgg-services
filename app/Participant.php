<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Participant extends Model
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
        'rank' => 'integer',
        'seed' => 'integer',
        'checked_in_at' => 'datetime',
    ];

    /**
     * Get the owning participantable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function participantable()
    {
        return $this->morphTo();
    }

    /**
     * Get the tournament that owns the participant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
