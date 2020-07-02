<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    ID,
    MorphTo,
    Text
};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Link extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Link::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'url';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'url',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            new Panel('Link Details', $this->details()),

            new Panel('Modifications', $this->modifications()),

            new Panel('Relations', $this->relations($request)),
        ];
    }

    /**
     * Resource detail fields.
     *
     * @return array
     */
    protected function details()
    {
        return  [
            ID::make()->sortable(),

            Text::make('URL')
                ->sortable()
                ->required()
                ->rules('required', 'active_url'),
        ];
    }

    /**
     * Resource relations.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    protected function relations(NovaRequest $request)
    {
        return [
            BelongsTo::make('Link Type', 'linkType')
                ->required()
                ->searchable()
                ->showCreateRelationButton($request->isUpdateOrUpdateAttachedRequest())
                ->readonly($request->isUpdateOrUpdateAttachedRequest()),

            MorphTo::make('Linkable')
                ->required()
                ->types([
                    Game::class,
                    Studio::class,
                    Team::class,
                    User::class,
                ]),
        ];
    }
}
