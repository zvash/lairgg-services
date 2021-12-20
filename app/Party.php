<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Party extends Model
{
    use Actionable;

    protected $fillable = [
        'play_id',
        'team_id',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $appends = [
        'participant_summary'
    ];

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
        'is_host' => false,
        'is_winner' => false,
    ];

    /**
     * Get the team that owns the party.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'team_id');
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

    /**
     * @return array|null
     */
    public function getParticipantSummaryAttribute()
    {
        if (! $this->team_id) {
            return null;
        }
        $participant = Participant::find($this->team_id);
        if ($participant->participantable_type == Team::class) {
            return [
                'title' => $participant->participantable->title,
                'image' => $participant->participantable->logo,
            ];
        } else if ($participant->participantable_type == User::class) {
            return [
                'title' => $participant->participantable->username,
                'image' => $participant->participantable->avatar,
            ];
        }
        return null;
    }
}
