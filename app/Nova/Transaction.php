<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Badge,
    BelongsTo,
    ID,
    MorphTo,
    Text
};
use Laravel\Nova\Panel;

class Transaction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Transaction::class;

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
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'value',
    ];

    /**
     * Determine if the current user can view the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $ability
     * @return bool
     */
    public function authorizedTo(Request $request, $ability)
    {
        return in_array($ability, ['view'])
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
            new Panel('Transaction Details', $this->details()),

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

            Text::make('Value', function () {
                switch ($this->valueType->title) {
                    case 'Point':
                        return $this->value.' Points';

                    case 'Cash':
                        return '$'.$this->value;

                    default:
                        return $this->value;
                }
            }),

            Badge::make('Type', function () {
                return $this->transactionable instanceof \App\Order
                    ? 'Order'
                    : 'Prize';
            })->map([
                'Order' => 'info',
                'Prize' => 'success',
            ]),

            Text::make('Created at', function () {
                return $this->created_at->diffForHumans();
            })->hideFromDetail(),
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

            MorphTo::make('Transactionable')
                ->types([
                    Order::class,
                    Participant::class,
                ]),
        ];
    }
}
