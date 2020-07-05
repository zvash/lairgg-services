<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Seo extends Model
{
    use Actionable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'keywords' => 'array',
    ];

    /**
     * Get the owning seoable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function seoable()
    {
        return $this->morphTo();
    }

    /**
     * Get the seo's formatted attribute.
     *
     * @return string|null
     */
    public function getFormattedKeywordsAttribute()
    {
        return implode(', ', array_values($this->keywords)) ?: null;
    }
}
