<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{
    Badge,
    BelongsTo,
    Country,
    ID,
    MorphMany,
    Place,
    Select,
    Text
};
use Laravel\Nova\Panel;

class Order extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Order::class;

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
        'name',
        'phone',
        'address',
        'state',
        'city',
        'country',
        'postal_code',
    ];

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
        return $this->product->title;
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return 'Shop';
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
            (new Panel('Order Details', $this->details()))->withToolbar(),

            new Panel('Shipping', $this->shipping()),

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

            Select::make('Status')
                ->displayUsingLabels()
                ->onlyOnForms()
                ->required()
                ->rules('required')
                ->options([
                    0 => 'Pending',
                    1 => 'Processing',
                    2 => 'Shipped',
                    3 => 'Cancel',
                ]),

            Badge::make('Status', function () {
                switch ($this->status) {
                    case 1:
                        return 'Processing';

                    case 2:
                        return 'Shipped';

                    case 3:
                        return 'Cancel';

                    default:
                        return 'Pending';
                }
            })->map([
                'Cancel' => 'danger',
                'Pending' => 'warning',
                'Processing' => 'info',
                'Shipped' => 'success',
            ])->exceptOnForms(),
        ];
    }

    /**
     * Resource shipping fields.
     *
     * @return array
     */
    protected function shipping()
    {
        return [
            Text::make('Name')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'max:254'),

            Text::make('Phone')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'max:30'),

            Place::make('Address')
                ->hideFromIndex()
                ->required()
                ->rules('required'),

            Text::make('Postal Code')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'max:254'),

            Text::make('State')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'max:254'),

            Text::make('City')
                ->hideFromIndex()
                ->required()
                ->rules('required', 'max:254'),

            Country::make('Country')
                ->hideFromIndex()
                ->searchable()
                ->required()
                ->rules('required', 'max:4'),
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
            BelongsTo::make('Product')
                ->showCreateRelationButton()
                ->required()
                ->searchable()
                ->withSubtitles(),

            BelongsTo::make('User')
                ->showCreateRelationButton()
                ->required()
                ->searchable()
                ->withSubtitles(),

            MorphMany::make('Transactions'),
        ];
    }
}
