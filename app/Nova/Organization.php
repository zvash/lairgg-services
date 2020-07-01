<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    Badge,
    Code,
    HasMany,
    ID,
    Image,
    Line,
    MorphMany,
    Select,
    Stack,
    Text,
    Timezone
};
use Laravel\Nova\Panel;

class Organization extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Organization::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'username',
    ];

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
            new Panel('Organization Details', $this->details()),

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

            Avatar::make('Logo')
                ->disk('s3')
                ->squared()
                ->path('organizations/logos')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

            Image::make('Cover')
                ->disk('s3')
                ->squared()
                ->hideFromIndex()
                ->path('organizations/covers')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

            Text::make('Name')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Text::make('Username')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'regex:/^[a-z\-]{4,50}$/i')
                ->creationRules('unique:organizations,username')
                ->updateRules('unique:organizations,username,{{resourceId}}'),

            Code::make('Bio')
                ->language('markdown')
                ->nullable()
                ->rules('nullable'),

            Stack::make('Details', [
                Line::make('username')->asSubTitle(),

                Line::make('timezone')
                    ->extraClasses('text-primary-dark font-bold')
                    ->asSmall(),
            ])->onlyOnIndex(),

            Timezone::make('Timezone')
                ->hideFromIndex()
                ->required()
                ->searchable(),

            Select::make('Status')
                ->displayUsingLabels()
                ->onlyOnForms()
                ->required()
                ->rules('required')
                ->options([
                    0 => 'Deactive',
                    1 => 'Active',
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
     * Resource relations.
     *
     * @return array
     */
    protected function relations()
    {
        return [
            HasMany::make('Staff'),

            MorphMany::make('Links'),
        ];
    }
}
