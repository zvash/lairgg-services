<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Play extends Model
{
    use Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = false;

    /**
     * Get the match that owns the play.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function match()
    {
        return $this->belongsTo(Match::class);
    }

    /**
     * Get the map that owns the play.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    /**
     * Get the edited by that owns the play.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Get the parties for the play.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parties()
    {
        return $this->hasMany(Party::class);
    }
}
