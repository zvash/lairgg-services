<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{
    protected $fillable = [
        'tournament_id',
        'user_id',
        'points',
    ];
}
