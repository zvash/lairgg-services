<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    BelongsTo,
    BelongsToMany,
    Code,
    Date,
    HasMany,
    ID,
    Image,
    MorphMany,
    MorphOne,
    Text
};
use Laravel\Nova\Panel;

class Game extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Game::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return $this->studio->title;
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Games';
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
            new Panel('Game Details', $this->details()),

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
                ->path('games/logos')
                ->prunable()
                ->deletable(false)
                ->required()
                ->creationRules('required')
                ->updateRules('nullable')
                ->rules('mimes:jpeg,jpg,png'),

            Image::make('Image')
                ->disk('s3')
                ->squared()
                ->hideFromIndex()
                ->path('games/images')
                ->prunable()
                ->deletable(false)
                ->required()
                ->creationRules('required')
                ->updateRules('nullable')
                ->rules('mimes:jpeg,jpg,png'),

            Image::make('Cover')
                ->disk('s3')
                ->squared()
                ->hideFromIndex()
                ->path('games/covers')
                ->prunable()
                ->deletable(false)
                ->required()
                ->creationRules('required')
                ->updateRules('nullable')
                ->rules('mimes:jpeg,jpg,png'),

            Text::make('Title')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Code::make('Bio')
                ->language('markdown')
                ->required()
                ->rules('required'),

            Date::make('Launched at')
                ->required()
                ->rules('required', 'date')
                ->format('Do MMMM YYYY'),
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
            BelongsTo::make('Studio')
                ->showCreateRelationButton()
                ->searchable()
                ->required(),

            BelongsTo::make('Game Type', 'gameType')
                ->showCreateRelationButton()
                ->searchable()
                ->required(),

            MorphMany::make('Links'),

            MorphOne::make('Seo'),

            HasMany::make('Maps'),

            HasMany::make('Teams'),

            BelongsToMany::make('Users')
                ->searchable()
                ->fields(function () {
                    return [
                        Text::make('Username')
                            ->required()
                            ->rules('required', 'max:254'),
                    ];
                }),
        ];
    }
}
