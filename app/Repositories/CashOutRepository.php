<?php

namespace App\Repositories;


use App\CashOut;
use App\Enums\CashOutStatus;
use App\User;
use Illuminate\Database\Eloquent\Builder;

class CashOutRepository extends BaseRepository
{
    /**
     * @param User $user
     * @param array $inputs
     * @return Builder|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function createPendingCashOut(User $user, array $inputs)
    {
        $points = $inputs['points'];
        $email = $inputs['paypal_email'];
        if ($points > $user->availablePoints()) {
            throw new \Exception('You don\'t have enough gems to register this cash out request.');
        }
        $inputs = [
            'user_id' => $user->id,
            'redeem_points' => $points,
            'cash_amount' => $points * config('cash_out.point_to_cash_rate'),
            'paypal_email' => $email,
            'status' => CashOutStatus::PENDING,
            'is_final' => false,
        ];

        return CashOut::query()->create($inputs);
    }
}
