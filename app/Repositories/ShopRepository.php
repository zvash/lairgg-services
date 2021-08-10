<?php

namespace App\Repositories;


use App\Enums\ProductStatus;
use App\Game;
use App\Product;
use App\User;
use Illuminate\Database\Eloquent\Builder;

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
}