<?php

namespace App\Repositories;

use App\User;

class UserRepository extends BaseRepository
{
    public $modelClass = User::class;
}