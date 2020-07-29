<?php

namespace App;

use App\Enums\Status;
use App\Traits\Eloquents\{
    Followable,
    Linkable,
    Participantable
};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Actions\Actionable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,
        HasApiTokens,
        SoftDeletes,
        Followable,
        Actionable,
        Linkable,
        Participantable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'dob' => 'date',
        'points' => 'integer',
        'status' => 'integer',
        'email_verified_at' => 'datetime',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'points' => 0,
        'status' => Status::ACTIVE,
        'timezone' => 'UTC',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the gender that owns the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    /**
     * Get the staff for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * The games that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function games()
    {
        return $this->belongsToMany(Game::class, 'usernames')
            ->using(Username::class)
            ->withPivot('username')
            ->withTimestamps();
    }

    /**
     * The teams that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'players')
            ->using(Player::class)
            ->withTimestamps()
            ->withPivot('captain');
    }

    /**
     * Get the transactions for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the orders for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the following for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function following()
    {
        return $this->hasMany(Follower::class);
    }

    /**
     * Get the join requests for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function joins()
    {
        return $this->hasMany(Join::class);
    }

    /**
     * Add or subtract point from the user.
     *
     * @param  int  $points
     */
    public function points(int $points)
    {
        $this->points += $points;

        return $this->save();
    }
}
