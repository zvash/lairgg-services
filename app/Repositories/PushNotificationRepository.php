<?php

namespace App\Repositories;


use App\CashOut;
use App\Enums\CashOutStatus;
use App\PushNotification;
use App\Team;
use App\User;
use Illuminate\Database\Eloquent\Builder;

class PushNotificationRepository extends BaseRepository
{
    /**
     * @param User $user
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function allForUser(User $user)
    {
        $paginated = $user->notifications()->paginate(20);
        $data = $paginated->toArray()['data'];
        $ids = [0];
        foreach ($data as $item) {
            $ids[] = $item['id'];
        }
        PushNotification::query()
            ->whereIn('id', $ids)
            ->update(['read_at' => \Carbon\Carbon::now()]);
        return $paginated;
    }
}
