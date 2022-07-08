<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchParticipant extends Model
{
    protected $fillable = [
        'match_id',
        'participant_id',
        'ready_at',
    ];

    protected $casts = [
        'ready_at' => 'datetime',
    ];

    protected $appends = [
        'ready_at_with_timezone',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function match()
    {
        return $this->belongsTo(Match::class, 'match_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    /**
     * @return mixed
     */
    public function getReadyAtWithTimezoneAttribute()
    {
        $readyAt = $this->ready_at;
        $timezone = request()->header('timezone');
        if ($readyAt && $timezone) {
            $minutes = convertTimeToMinutes($timezone);
            return $readyAt->addMinutes($minutes);
        }
        return $readyAt;
    }
}
