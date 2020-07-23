<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    HasMany,
    ID,
    Image
};
use Laravel\Nova\Panel;

class Play extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Play::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->match->tournament->title;
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
            (new Panel('Play Details', $this->details()))->withToolbar(),

            new Panel('Modifications', $this->modifications()),

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

            Image::make('Screenshot')
                ->hideFromIndex()
                ->disk('s3')
                ->squared()
                ->path('plays/screenshots')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png'),
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
            BelongsTo::make('Match')
                ->readonly(),

            BelongsTo::make('Map')
                ->searchable()
                ->withSubtitles()
                ->showCreateRelationButton()
                ->nullable(),

            BelongsTo::make('Edited by', 'editedBy', User::class)
                ->showCreateRelationButton()
                ->searchable()
                ->nullable()
                ->withSubtitles(),

            HasMany::make('Parties'),
        ];
    }
}
