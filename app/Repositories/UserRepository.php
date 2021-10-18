<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\User;

class UserRepository extends BaseRepository
{
    public $modelClass = User::class;

    /**
     * @param User $user
     * @param string $status
     * @param int $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function getUserOrders(User $user, string $status = 'all', int $paginate = 10)
    {
        $statusMap = [
            'preparing' => OrderStatus::PENDING,
            'processing' => OrderStatus::PROCESSING,
            'shipped' => OrderStatus::SHIPPED,
            'cancelled' => OrderStatus::CANCEL
        ];
        $userOrders = $user->orders()->with('product');

        if (array_key_exists($status, $statusMap)) {
            $userOrders = $userOrders->where('status', $statusMap[$status]);
        }

        if ($paginate) {
            return $userOrders
                ->orderBy('id', 'desc')
                ->paginate($paginate);
        }

        return $userOrders
            ->orderBy('id', 'desc')
            ->get();
    }
}
