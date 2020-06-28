<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    Date,
    DateTime,
    ID,
    Image,
    Line,
    Number,
    Password,
    PasswordConfirmation,
    Stack,
    Text,
    Textarea,
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
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->full_name;
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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            new Panel('User Details', $this->details()),

            new Panel('Security and Privacy', $this->securityAndPrivacy()),

            new Panel('Tournaments', $this->tournaments()),

            new Panel('Modifications', $this->modifications(true)),
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
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

            Image::make('Cover')
                ->hideFromIndex()
                ->disk('s3')
                ->squared()
                ->path('users/covers')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

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
                ->rules('required', 'email:rfc,dns', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make('Username')
                ->sortable()
                ->required()
                ->rules('required', 'regex:/^[\w]{4,50}$/i')
                ->creationRules('unique:users,username')
                ->updateRules('unique:users,username,{{resourceId}}'),

            Stack::make('Details', [
                Line::make('email')->asSubTitle(),

                Line::make('timezone')
                    ->extraClasses('text-primary-dark font-bold')
                    ->asSmall(),

                Line::make('dob', function () {
                    return $this->dob ? 'Date of Birth: '.$this->dob->toDateString() : null;
                })->asSmall(),
            ])->onlyOnIndex(),

            Textarea::make('Bio')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable'),

            Date::make('Date of Birth', 'dob')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'date'),

            Timezone::make('Timezone')
                ->hideFromIndex()
                ->searchable(),
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
                ->sortable()
                ->readonly()
                ->exceptOnForms(),
        ];
    }
}
