<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    Boolean,
    ID,
    MorphTo,
    Text
};
use Laravel\Nova\Panel;

class Join extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Join::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Determine if the current user can view the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ability
     * @return bool
     */
    public function authorizedTo(Request $request, $ability)
    {
        return in_array($ability, ['view', 'delete'])
            ? parent::authorizedTo($request, $ability)
            : false;
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
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
            (new Panel('Join Request Details', $this->details()))->withToolbar(),

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

            Text::make('Created at', function () {
                return $this->created_at->diffForHumans();
            })->hideFromDetail(),

            Boolean::make('Via URL')
                ->sortable(),
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
            BelongsTo::make('User'),

            MorphTo::make('Joinable')
                ->types([
                    Tournament::class,
                    Team::class,
                ]),
        ];
    }
}
