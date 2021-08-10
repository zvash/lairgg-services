<?php

namespace App\Jobs;

use App\Order;
use App\Product;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessNotFinalOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $orders = Order::query()
            ->where('is_final', false)
            ->orderBy('created_at')
            ->get();
        $toDeleteIds = [];
        foreach ($orders as $order) {
            try {
                DB::beginTransaction();
                $product = Product::query()->find($order->product_id);
                $user = User::query()->find($order->user_id);
                if ($product->quantity >= 1 && $user->points >= $order->redeem_points) {
                    $product->quantity = $product->quantity - 1;
                    $user->points = $user->points - $order->redeem_points;
                    $order->is_final = true;
                    $product->save();
                    $user->save();
                    $order->save();
                } else {
                    $toDeleteIds[] = $order->id;
                }
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
            }
        }
        Order::query()->whereIn('id', $toDeleteIds)->delete();
    }
}
