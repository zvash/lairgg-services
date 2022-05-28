<?php

namespace App\Nova;

use App\Enums\Status;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    Badge,
    BelongsTo,
    BelongsToMany,
    Boolean,
    Code,
    Country,
    Date,
    DateTime,
    HasMany,
    ID,
    Image,
    Line,
    MorphMany,
    Number,
    Password,
    PasswordConfirmation,
    Place,
    Select,
    Stack,
    Text,
    Timezone
};
use Laravel\Nova\Panel;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\User::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'first_name',
        'last_name',
        'email',
        'username',
    ];

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->username;
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return $this->username;
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Accounts';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            (new Panel('User Details', $this->details()))->withToolbar(),

            new Panel('Security and Privacy', $this->securityAndPrivacy()),

            new Panel('Tournaments', $this->tournaments()),

            new Panel('Modifications', $this->modifications(true)),

            new Panel('Relations', $this->relations()),
        ];
    }

    /**
     * Resource detail fields.
     *
     * @return array
     */
    protected function details()
    {
        return [
            ID::make()->sortable(),

            Avatar::make('Avatar')
                ->disk('s3')
                ->squared()
                ->path('users/avatars')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png,webp'),

            Image::make('Cover')
                ->hideFromIndex()
                ->disk('s3')
                ->squared()
                ->path('users/covers')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png,webp'),

            Text::make('Full name', function () {
                return $this->title();
            })->exceptOnForms(),

            Text::make('First name')
                ->onlyOnForms()
                ->required()
                ->rules('required', 'max:50'),

            Text::make('Last name')
                ->onlyOnForms()
                ->required()
                ->rules('required', 'max:50'),

            Text::make('Email')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'email:rfc', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make('Username')
                ->sortable()
                ->required()
                ->rules('required', 'regex:/^[\w]{4,50}$/i')
                ->creationRules('unique:users,username')
                ->updateRules('unique:users,username,{{resourceId}}'),

            Stack::make('Details', [
                Line::make('Email')->asSubTitle(),

                Line::make('Timezone')
                    ->extraClasses('text-primary-dark font-bold')
                    ->asSmall(),

                Line::make('Points', function () {
                    return view('nova::partials.status', [
                        'points' => $this->points,
                    ])->render();
                })->asHtml(),
            ])->onlyOnIndex(),

            Code::make('Bio')
                ->language('markdown')
                ->nullable()
                ->rules('nullable'),

            Date::make('Date of Birth', 'dob')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'date')
                ->format('Do MMMM YYYY'),

            Text::make('Phone')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'max:30'),

            Place::make('Address')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable'),

            Text::make('Postal Code')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'max:254'),

            Text::make('State')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'max:254'),

            Text::make('City')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'max:254'),

            Country::make('Country')
                ->hideFromIndex()
                ->searchable()
                ->nullable()
                ->rules('nullable', 'max:4'),

            Timezone::make('Timezone')
                ->required()
                ->hideFromIndex()
                ->searchable(),

            Select::make('Status')
                ->displayUsingLabels()
                ->onlyOnForms()
                ->required()
                ->rules('required')
                ->options([
                    Status::DEACTIVE => 'Deactive',
                    Status::ACTIVE => 'Active',
                ]),

            Badge::make('Status', function () {
                return $this->status ? 'Active' : 'Deactivated';
            })->map([
                'Deactivated' => 'danger',
                'Active' => 'success',
            ])->exceptOnForms(),
        ];
    }

    /**
     * Resource security and privacy fields.
     *
     * @return array
     */
    protected function securityAndPrivacy()
    {
        return [
            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8'),

            PasswordConfirmation::make('Password Confirmation'),

            Text::make('IP Address', 'ip')
                ->readonly()
                ->onlyOnDetail(),

            DateTime::make('Email verified at')
                ->readonly()
                ->onlyOnDetail(),
        ];
    }

    /**
     * Resource tournament fields.
     *
     * @return array
     */
    protected function tournaments()
    {
        return [
            Number::make('Points')
                ->readonly()
                ->onlyOnDetail(),
        ];
    }

    /**
     * Resource relations.
     *
     * @return array
     */
    protected function relations()
    {
        return [
            BelongsTo::make('Gender')
                ->hideFromIndex()
                ->searchable()
                ->showCreateRelationButton()
                ->nullable(),

            MorphMany::make('Links'),

            BelongsToMany::make('Games')
                ->searchable()
                ->fields(function () {
                    return [
                        Text::make('Username')
                            ->required()
                            ->rules('required', 'max:254'),
                    ];
                }),

            BelongsToMany::make('Teams')
                ->searchable()
                ->fields(function () {
                    return [
                        Boolean::make('Captain'),
                    ];
                }),

            MorphMany::make('Followers'),

            HasMany::make('Following', 'following', Follower::class),

            MorphMany::make('Participants'),

            HasMany::make('Orders'),

            HasMany::make('Transactions'),
        ];
    }
}
