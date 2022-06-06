<?php

namespace App\Policies;

use App\CashOut;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CashOutPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param CashOut $cashOut
     * @return Response
     */
    public function get(User $user, CashOut $cashOut)
    {
        return $user->id == $cashOut->user_id
            ? Response::allow()
            : Response::deny(__('strings.policy.cash_out_read_access'));
    }
}
