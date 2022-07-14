<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PickBanTimeout extends Model
{
    protected $fillable = [
        'lobby_name',
        'user_id',
        'current_step',
        'random_arguments',
        'manually_selected',
    ];

    protected $casts = [
        'arguments' => 'array',
    ];
}
