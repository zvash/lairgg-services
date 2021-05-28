<?php

namespace App;

use App\Traits\Eloquents\{
    Followable, InviteAware, Joinable, Linkable, Participantable
};
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use Laravel\Nova\Actions\Actionable;

class Team extends Model
{
    use SoftDeletes,
        Actionable,
        Followable,
        Linkable,
        Joinable,
        Participantable,
        InviteAware;

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
        'join_request' => 'boolean',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'join_request' => false,
    ];

    /**
     * Get the game that owns the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get users that belong to the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function players()
    {
        return $this->belongsToMany(User::class, 'players')
            ->using(Player::class)
            ->withTimestamps()
            ->withPivot(['id', 'captain'])
            ->select([
                'players.id',
                'user_id',
                'team_id',
                'username',
                'avatar',
                'cover',
                'points',
                'players.captain'
            ]);
    }

    /**
     * Get the parties for the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parties()
    {
        return $this->hasMany(Party::class);
    }
}
