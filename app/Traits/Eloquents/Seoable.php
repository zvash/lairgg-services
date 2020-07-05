<?php

namespace App\Traits\Eloquents;

use App\Seo;

trait Seoable
{
    /**
     * Get the resource's seo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function seo()
    {
        return $this->morphOne(Seo::class, 'seoable');
    }
}
