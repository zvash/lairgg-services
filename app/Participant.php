<?php

namespace App;

use App\Traits\Eloquents\Transactionable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Participant extends Model
{
    use Actionable, Transactionable;

    protected $fillable = [
        'tournament_id',
        'participantable_type',
        'participantable_id',
        'status',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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

    /**
     * Get the prize that owns the participant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }

    /**
     * @return null
     */
    public function getName()
    {
        if ($this->participantable_type == User::class) {
            $user = User::find($this->participantable_id);
            if ($user) {
                return $user->username;
            }
        } else if ($this->participantable_type == Team::class) {
            $team = Team::find($this->participantable_id);
            if ($team) {
                return $team->title;
            }
        }
        return null;
    }

    /**
     * @return null
     */
    public function getAvatar()
    {
        if ($this->participantable_type == User::class) {
            $user = User::find($this->participantable_id);
            if ($user) {
                return $user->avatar;
            }
        } else if ($this->participantable_type == Team::class) {
            $team = Team::find($this->participantable_id);
            if ($team) {
                return $team->logo;
            }
        }
        return null;
    }
}
