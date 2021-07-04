<?php

namespace App\Traits\Eloquents;


use App\Banner;

trait Bannerable
{
    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function banners()
    {
        return $this->morphMany(Banner::class, 'bannerable');
    }
}