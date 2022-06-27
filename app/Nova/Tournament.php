<?php

namespace App\Nova;

use App\Nova\Actions\AutoWinRestMatches;
use App\Nova\Actions\MakeAnnouncement;
use App\Nova\Actions\ReleaseRandomBracket;
use App\Enums\{
    Platform, Status, TournamentStructure
};
use App\Nova\Filters\Featured;
use App\Nova\Filters\FeaturedTournament;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Avatar,
    Badge,
    BelongsTo,
    Boolean,
    Code,
    Currency,
    DateTime,
    HasMany,
    ID,
    Image,
    Line,
    MorphMany,
    Number,
    Select,
    Stack,
    Text,
    Timezone
};
use Laravel\Nova\Panel;

class Tournament extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Tournament::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    public function filters(Request $request)
    {
        return[
            new FeaturedTournament
        ];
    }
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'rules',
        'structure',
        'description',
    ];

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return $this->tournamentType->title;
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
            (new Panel('Tournament Details', $this->details()))->withToolbar(),

            new Panel('Configuration', $this->configuration()),

            new Panel('Match Configuration', $this->matchConfiguration()),

            new Panel('League Configuration', $this->leagueConfiguration()),

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

            Avatar::make('Image')
                ->disk('s3')
                ->squared()
                ->path('tournaments/images')
                ->prunable()
                ->deletable(false)
                ->required()
                ->creationRules('required')
                ->updateRules('nullable')
                ->rules('mimes:jpeg,jpg,png,webp'),

            Image::make('Cover')
                ->hideFromIndex()
                ->disk('s3')
                ->squared()
                ->path('tournaments/covers')
                ->prunable()
                ->deletable()
                ->nullable()
                ->rules('nullable', 'mimes:jpeg,jpg,png,webp'),

            Text::make('Title')
                ->required()
                ->rules('required', 'max:254'),

            Code::make('Description')
                ->language('markdown')
                ->nullable()
                ->rules('nullable'),

            Code::make('Rules')
                ->help('You can use this fields to Describe all the tournament rules.')
                ->language('markdown')
                ->nullable()
                ->rules('nullable'),

            Timezone::make('Timezone')
                ->required()
                ->hideFromIndex()
                ->searchable(),
        ];
    }

    /**
     * Resource configuration fields.
     *
     * @return array
     */
    protected function configuration()
    {
        return [

            Number::make('Min Teams')
                ->help('Minimum number of participate teams.')
                ->min(0)
                ->required()
                ->rules('required', 'integer', 'gte:0'),

            Number::make('Max Teams')
                ->help('Maximum number of participate teams.')
                ->min(1)
                ->required()
                ->rules('required', 'integer', 'gte:1'),

            Number::make('Reserve Teams')
                ->help('Number of reserve teams for the tournament.')
                ->hideFromIndex()
                ->min(0)
                ->required()
                ->rules('required', 'integer', 'gte:0'),

            Number::make('Players')
                ->help('Number of players for each team.')
                ->hideFromIndex()
                ->min(1)
                ->required()
                ->rules('required', 'integer', 'gte:1'),

            Number::make('Check in period')
                ->help('Number of minutes each team needs to check in before.')
                ->hideFromIndex()
                ->min(1)
                ->required()
                ->rules('required', 'integer', 'gte:1'),

            Currency::make('Entry fee')
                ->currency('USD')
                ->hideFromIndex()
                ->help('Entry fee for the tournament.')
                ->required()
                ->rules('required', 'numeric', 'gte:0'),

            Boolean::make('Listed')
                ->hideFromIndex()
                ->help('Private or public?'),

            Boolean::make('Join request')
                ->hideFromIndex()
                ->help('Teams can join the tournaments via the invitation link.'),

            Boolean::make('Featured')
                ->help('Make tournament featured.'),

            Boolean::make('Requires Score', 'requires_score')
                ->hideFromIndex()
                ->default(true)
                ->help('Need to submit score manually for each play?'),

            Text::make('Join URL')
                ->readonly()
                ->onlyOnDetail(),

            Select::make('Structure')
                ->displayUsingLabels()
                ->required()
                ->rules('required')
                ->options([
                    TournamentStructure::SIX => '6v6',
                    TournamentStructure::FIVE => '5v5',
                    TournamentStructure::FOUR => '4v4',
                    TournamentStructure::THREE => '3v3',
                    TournamentStructure::TWO => '2v2',
                    TournamentStructure::ONE => '1v1',
                    TournamentStructure::OTHER => 'Other',
                ]),

            Select::make('Platform')
                ->displayUsingLabels()
                ->required()
                ->rules('required')
                ->options([
                    Platform::PC => Platform::PC,
                    Platform::CONSOLE => Platform::CONSOLE,
                    Platform::MOBILE => Platform::MOBILE,
                ]),


            DateTime::make('Started at')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'date'),

            DateTime::make('Bracket Released At', 'bracket_released_at')
                ->hideFromIndex()
                ->nullable()
                ->rules('nullable', 'date', 'before:started_at'),

            DateTime::make('Ended at')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'date', 'after:started_at'),

            Stack::make('Details', [
                Line::make('Tournament Stage', function () {
                    return $this->tournamentType->stage;
                })->asSubTitle(),

                Line::make('Tournament Type', function () {
                    return $this->tournamentType->title;
                })->extraClasses('text-primary-dark font-bold')->asSmall(),

                Line::make('Timezone', function () {
                    return $this->timezone;
                })->asSmall(),

                Line::make('Game & Organization', function () {
                    return $this->game->title.' - '.$this->organization->title;
                })->asSmall(),
            ])->onlyOnIndex(),

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
     * Resource match configuration fields.
     *
     * @return array
     */
    protected function matchConfiguration()
    {
        return [
            Number::make('Check in period', 'match_check_in_period')
                ->hideFromIndex()
                ->help('Number of minutes each team needs to check in before the match.')
                ->min(1)
                ->required()
                ->rules('required', 'integer', 'gte:1'),

            Number::make('Play count', 'match_play_count')
                ->hideFromIndex()
                ->help('BO (aka Best of), like BO5 or BO3 ...')
                ->min(1)
                ->required()
                ->rules('required', 'integer', 'gte:1'),

            Boolean::make('Randomize map', 'match_randomize_map')
                ->hideFromIndex()
                ->help('Tournament builder will randomize the map for the matches.'),

            Boolean::make('Third rank', 'match_third_rank')
                ->hideFromIndex()
                ->help('Tournament builder will create a match for 3rd rank.'),
        ];
    }

    /**
     * Resource league configuration fields.
     *
     * @return array
     */
    protected function leagueConfiguration()
    {
        return [
            Number::make('Win score', 'league_win_score')
                ->hideFromIndex()
                ->help('League winning score.')
                ->min(0)
                ->nullable()
                ->rules('nullable', 'required_if:tournamentType,4', 'integer', 'gte:0'),

            Number::make('Tie score', 'league_tie_score')
                ->hideFromIndex()
                ->help('League tie score.')
                ->min(0)
                ->nullable()
                ->rules('nullable', 'required_if:tournamentType,4', 'integer', 'gte:0'),

            Number::make('Lose score', 'league_lose_score')
                ->hideFromIndex()
                ->help('League lose score.')
                ->min(0)
                ->nullable()
                ->rules('nullable', 'required_if:tournamentType,4', 'integer', 'gte:0'),

            Number::make('Match-up count', 'league_match_up_count')
                ->hideFromIndex()
                ->help('Number of matches each team will do with other team.')
                ->min(1)
                ->nullable()
                ->rules('nullable', 'required_if:tournamentType,4', 'integer', 'gte:1'),
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
            BelongsTo::make('Tournament Type', 'tournamentType')
                ->hideFromIndex()
                ->showCreateRelationButton()
                ->required()
                ->searchable()
                ->withSubtitles(),

            BelongsTo::make('Region')
                ->hideFromIndex()
                ->showCreateRelationButton()
                ->required()
                ->searchable(),

            BelongsTo::make('Organization')
                ->hideFromIndex()
                ->showCreateRelationButton()
                ->required()
                ->searchable()
                ->withSubtitles(),

            BelongsTo::make('Game')
                ->hideFromIndex()
                ->showCreateRelationButton()
                ->required()
                ->searchable()
                ->withSubtitles(),

            MorphMany::make('Links'),

            HasMany::make('Prizes'),

            MorphMany::make('Joins'),

            HasMany::make('Participants'),

            HasMany::make('Matches'),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new ReleaseRandomBracket())
                ->confirmText('Do you want to create a new bracket and release it?')
                ->confirmButtonText('Yes')
                ->cancelButtonText('No')
                ->showOnTableRow()
                ->showOnDetail(),

            (new MakeAnnouncement())
                ->confirmText('Do you want to make an announcement for this tournament?')
                ->confirmButtonText('Announce!')
                ->cancelButtonText('Cancel')
                ->showOnTableRow()
                ->showOnDetail(),

            (new AutoWinRestMatches())
                ->confirmText('Do you want to auto-win current rest matches?')
                ->confirmButtonText('Yes')
                ->cancelButtonText('No')
                ->showOnTableRow()
                ->showOnDetail(),
        ];
    }
}
