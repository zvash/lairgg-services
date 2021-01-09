<?php

namespace App\Policies;

use App\StaffType;
use App\User;
use App\Organization;
use \Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\User  $user
     * @param  string  $ability
     * @return bool
     */
    public function before(User $user, $ability)
    {
        return in_array($user->email, [
            'hossein@edoramedia.com',
            'ali.shafiee@edoramedia.com',
            'ilyad@edoramedia.com',
            'farbod@edoramedia.com'
        ]);
    }
    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Organization  $organization
     * @return mixed
     */
    public function update(User $user, Organization $organization)
    {
        $staffTypeId = 1;
        $staffType = StaffType::where('title', 'Admin')->first();
        if ($staffType) {
            $staffTypeId = $staffType->id;
        }
        $isAdmin = $organization
            ->staff()
            ->where('staff_type_id', $staffTypeId)
            ->where('user_id', $user->id)
            ->count();
        return $isAdmin
            ? Response::allow()
            : Response::deny('You are not an admin for this organization.');
    }

}
