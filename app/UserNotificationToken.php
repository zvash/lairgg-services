<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserNotificationToken extends Model
{
    protected $fillable = [
        'user_id',
        'passport_token',
        'platform',
        'token',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];
}
