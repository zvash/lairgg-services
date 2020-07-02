<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    BelongsTo,
    Boolean,
    ID
};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Staff extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Staff::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

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
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            new Panel('Staff Details', $this->details()),

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
        return [
            ID::make()->sortable(),

            Boolean::make('Owner'),
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
            BelongsTo::make('User')
                ->showCreateRelationButton($request->isUpdateOrUpdateAttachedRequest())
                ->readonly($request->isUpdateOrUpdateAttachedRequest())
                ->searchable()
                ->required(),

            BelongsTo::make('Staff Type', 'staffType')
                ->showCreateRelationButton()
                ->searchable()
                ->required(),

            BelongsTo::make('Organization')
                ->showCreateRelationButton()
                ->searchable()
                ->required(),
        ];
    }
}
