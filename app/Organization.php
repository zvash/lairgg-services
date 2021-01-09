<?php

namespace App;

use App\Enums\Status;
use App\Traits\Eloquents\Linkable;
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Organization extends Model
{
    use SoftDeletes, Actionable, Linkable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'title',
        'slug',
        'bio',
        'timezone',
        'logo',
        'cover',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'logo_full_url',
        'cover_full_url',
    ];

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
        'status' => 'integer',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => Status::ACTIVE,
        'timezone' => 'UTC',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the staff for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the tournaments for the organizations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }

    /**
     * @return null|string
     */
    public function getLogoFullUrlAttribute()
    {
        if ($this->logo) {
            $baseUrl = rtrim(env('AWS_URL'), '/') . '/';
            return $baseUrl . $this->logo;
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getCoverFullUrlAttribute()
    {
        if ($this->cover) {
            $baseUrl = rtrim(env('AWS_URL'), '/') . '/';
            return $baseUrl . $this->cover;
        }
        return null;
    }
}
