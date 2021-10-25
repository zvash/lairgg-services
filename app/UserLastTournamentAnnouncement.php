<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLastTournamentAnnouncement extends Model
{
    protected $fillable = [
        'user_id',
        'tournament_id',
        'tournament_announcement_id',
    ];
}
