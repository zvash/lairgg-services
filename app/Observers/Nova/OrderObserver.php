<?php

namespace App\Observers\Nova;

use App\Order;
use App\Traits\Observers\Validator;

class OrderObserver
{
    use Validator;

    /**
     * Handle the order "creating" event.
     *
     * @param  \App\Order  $order
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function creating(Order $order)
    {
        $this->validate($order);

        $order->redeem_points = $order->product->points;
    }

    /**
     * Handle the order "created" event.
     *
     * @param  \App\Order  $order
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function created(Order $order)
    {
        $this->sync($order, $order->product->points * -1);
    }

    /**
     * Handle the order "updated" event.
     *
     * @param  \App\Order  $order
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updated(Order $order)
    {
        if ($order->isClean('status')) {
            return;
        }

        if ($order->isCanceled()) {
            $this->sync($order, $order->redeem_points);
        }

        if ($order->isCanceledBefore()) {
            $this->sync($order, $order->redeem_points * -1);
        }
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param  \App\Order  $order
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleted(Order $order)
    {
        if (! $order->isCanceled()) {
            $this->sync($order, $order->redeem_points);
        }

        $order->transactions()->delete();
    }

    /**
     * Sync user points and add a transaction.
     *
     * @param  \App\Order  $order
     * @param  int  $points
     * @return \Illuminate\Database\Eloquent\Model|false
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function sync(Order $order, int $points)
    {
        $order->user->points($points);

        $action = $points > 0 ? 'increment' : 'decrement';

        $order->product->{$action}('quantity');

        return $order->addTransaction($order->user, $points, 'Point');
    }

    /**
     * The data array for validator.
     *
     * @return array
     */
    protected function data()
    {
        return ['user' => $this->resource->user->points >= $this->resource->product->points];
    }

    /**
     * The rules array for validator.
     *
     * @return array
     */
    protected function rules()
    {
        return ['user' => 'boolean|accepted'];
    }

    /**
     * The messages array for validator.
     *
     * @return array
     */
    protected function messages()
    {
        return ['user.accepted' => 'This :attribute doesn\'t have enough points to redeem.'];
    }
}
