<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    BelongsTo,
    BelongsToMany,
    Boolean,
    Code,
    ID,
    Image,
    MorphMany,
    Text
};
use Laravel\Nova\Panel;

class Team extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Team::class;

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
        return $this->game->title;
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Tournaments';
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
            (new Panel('Team Details', $this->details()))->withToolbar(),

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
                ->path('teams/logos')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

            Image::make('Cover')
                ->disk('s3')
                ->squared()
                ->hideFromIndex()
                ->path('teams/covers')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),

            Text::make('Title')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Code::make('Bio')
                ->language('markdown')
                ->nullable()
                ->rules('nullable'),

            Boolean::make('Join request')
                ->help('Users can send a join request or not?'),
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
            BelongsTo::make('Game')
                ->showCreateRelationButton()
                ->searchable()
                ->withSubtitles()
                ->required(),

            MorphMany::make('Links'),

            BelongsToMany::make('Players', 'players', User::class)
                ->searchable()
                ->fields(function () {
                    return [
                        Boolean::make('Captain'),
                    ];
                }),

            MorphMany::make('Followers'),

            MorphMany::make('Joins'),

            MorphMany::make('Participants'),
        ];
    }
}
