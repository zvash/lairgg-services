<?php

namespace App;

use App\Traits\Eloquents\Bannerable;
use Illuminate\Database\Eloquent\Model;

class BannerUrl extends Model
{
    use Bannerable;
}
