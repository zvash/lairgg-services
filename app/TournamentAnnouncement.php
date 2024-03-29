<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TournamentAnnouncement extends Model
{
    protected $fillable = [
        'tournament_id',
        'user_id',
        'content'
    ];

    protected $appends = [
        'creator',
        'is_new',
        'sent_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return bool
     */
    public function getIsNewAttribute()
    {
        $user = request()->user();
        if (! $user) {
            return true;
        }
        $lastRead = $user->lastTournamentAnnouncement()
            ->where('tournament_id', $this->tournament_id)
            ->first();
        if (! $lastRead) {
            return true;
        }
        return $this->id > $lastRead->tournament_announcement_id;
    }

    /**
     * @return mixed
     */
    public function getCreatorAttribute()
    {
        return $this->staff->username;
    }

    /**
     * @return mixed
     */
    public function getSentAtAttribute()
    {
        $createdAt = $this->created_at;
        $timezone = request()->header('timezone');
        if ($timezone) {
            $minutes = convertTimeToMinutes($timezone);
            return $createdAt->addMinutes($minutes);
        }
        return $createdAt;
    }
}
