<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Seo extends Model
{
    use Actionable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

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
