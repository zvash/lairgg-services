<?php

namespace App;

use App\Enums\Status;
use App\Notifications\CustomResetPassword;
use App\Repositories\CountryRepository;
use App\Traits\Eloquents\{
    Followable,
    Linkable,
    Participantable
};
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Tests\Fixtures\Role;
use Laravel\Passport\HasApiTokens;
use \Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable implements MustVerifyEmail
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
        'email',
        'delete_reason',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
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

    protected $appends = [
        'email_address',
        'country_detail',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function socialMediaAccounts()
    {
        return $this->hasMany(SocialMediaAccount::class);
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pushNotifications()
    {
        return $this->hasMany(PushNotification::class, 'user_id');
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
            ->withPivot(['id', 'captain']);
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cashOuts()
    {
        return $this->hasMany(CashOut::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderRequests()
    {
        return $this->hasMany(OrderRequest::class);
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastTournamentAnnouncement()
    {
        return $this->hasMany(UserLastTournamentAnnouncement::class);
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

    /**
     * @return mixed
     */
    public function availablePoints()
    {
        $pendingOrdersPoints = $this->orders()->where('is_final', false)->sum('redeem_points');
        $pendingCashOutPoints = $this->cashOuts()->where('is_final', false)->sum('redeem_points');
        return $this->points - $pendingCashOutPoints - $pendingOrdersPoints;
    }

    /**
     * Register user process.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  array  $attributes
     * @return \App\User
     *
     * @throws \Exception
     */
    public function scopeRegister(Builder $builder, array $attributes)
    {
        try {
            DB::beginTransaction();

            $attributes['password'] = bcrypt($attributes['password']);

            //$user = tap($builder->create($attributes));
            $user = $builder->create($attributes);
                //->assignRole(Role::findByName('OAuth User', 'api'))
                //->loadMissing($this->with);

            DB::commit();

            return $user;
        } catch (\Exception $exception) {
            // If any exception throw we will roll back all changes and
            // Then rethrow it so ExceptionHandler class can resolve it
            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $notification = new CustomResetPassword($token);

        $this->notify($notification);
    }

    /**
     * Override the field which is used for username in the authentication
     * @param string $username
     * @return User
     */
    public function findForPassport(string $username)
    {
        return $this->where('email', $username)->orWhere('username', $username)->first();
    }

    /**
     * @param string $username
     * @return mixed
     */
    public static function findByUserName(string $username)
    {
        return static::where('email', $username)->orWhere('username', $username)->first();
    }

    /**
     * @return mixed|null
     */
    public function getEmailAddressAttribute()
    {
        $user = request()->user();
        if ($user && $user->id == $this->id) {
            return $this->email;
        }
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getCountryDetailAttribute()
    {
        if (! $this->country) {
            return null;
        }
        return (new CountryRepository())->getCountry($this->country);
    }
}
