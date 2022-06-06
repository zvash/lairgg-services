<?php

namespace App\Repositories;


use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Game;
use App\Order;
use App\Product;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ShopRepository extends BaseRepository
{
    /**
     * @param string $sortBy
     * @param string $direction
     * @param int $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function productList($sortBy = 'is_featured', $direction = 'desc', $paginate = 10)
    {
        if (! in_array($sortBy, ['is_featured', 'points'])) {
            $sortBy = 'is_featured';
        }
        $list = Product::query()
            ->where(function (Builder $query) {
                return $query->where('status', ProductStatus::ACTIVE)
                    ->where('quantity', '>', 0);
            })
            ->orWhere(function (Builder $query) {
                return $query->where('status', ProductStatus::COMING_SOON);
            })
            ->with('images')
            ->orderBy($sortBy, $direction);
        if ($paginate) {
            return $list->paginate($paginate);
        }
        return $list->get();
    }

    /**
     * @param User $user
     * @param array $inputs
     * @param CountryRepository $repository
     * @return Builder|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function createPendingOrder(User $user, array $inputs, CountryRepository $repository)
    {
        $product = Product::query()->find($inputs['product_id']);
        if ($product->quantity - $product->orders()->where('is_final', false)->count() < 1) {
            throw new \Exception(__('strings.shop.out_of_stock'));
        }
        if ($product->points > $user->availablePoints()) {
            throw new \Exception(__('strings.shop.not_enough_gems'));
        }
        $inputs['redeem_points'] = $product->points;
        $inputs['user_id'] = $user->id;
        $inputs['country'] = $repository->getAlpha2($inputs['country']);
        $inputs['status'] = OrderStatus::PENDING;
        $inputs['is_final'] = false;
        return Order::query()->create($inputs);
    }
}
