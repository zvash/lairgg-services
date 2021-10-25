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
        'is_new',
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
}
