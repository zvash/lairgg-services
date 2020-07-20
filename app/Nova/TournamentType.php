<?php

namespace App\Nova;

use App\Enums\TournamentStage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Badge,
    HasMany,
    ID,
    Select,
    Text
};
use Laravel\Nova\Panel;

class TournamentType extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\TournamentType::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

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
        return $this->stage;
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Types';
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
            (new Panel('Tournament Type Details', $this->details()))->withToolbar(),

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

            Text::make('Title')
                ->sortable()
                ->required()
                ->rules('required', 'max:254'),

            Select::make('Stage')
                ->onlyOnForms()
                ->required()
                ->rules('required')
                ->options([
                    TournamentStage::Dual => 'Dual',
                    TournamentStage::FFA => 'Free for All',
                ]),

            Badge::make('Stage', function () {
                return $this->stage;
            })->map([
                TournamentStage::FFA => 'info',
                TournamentStage::Dual => 'success',
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
            HasMany::make('Tournaments'),
        ];
    }
}
