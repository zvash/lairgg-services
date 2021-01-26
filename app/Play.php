<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

/**
 * @property Match match
 */
class Play extends Model
{
    use Actionable;

    protected $fillable = [
        'match_id'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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
     * Get parties of the play.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parties()
    {
        return $this->hasMany(Party::class);
    }

    /**
     * Get disputes of the play.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }
}
