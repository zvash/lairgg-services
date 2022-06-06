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
            'farbod@edoramedia.com',
            'siavash@lair.gg',
            'ace@lair.gg',
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
            : Response::deny(__('strings.policy.organization_edit_access'));
    }

    /**
     * Determine whether the user can add an admin to the organization.
     *
     * @param User $user
     * @param Organization $organization
     * @return Response
     */
    public function addAdmin(User $user, Organization $organization)
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
            : Response::deny(__('strings.policy.organization_edit_access'));
    }

    /**
     * Determine whether the user can add a moderator to the organization.
     *
     * @param User $user
     * @param Organization $organization
     * @return Response
     */
    public function addModerator(User $user, Organization $organization)
    {
        $staffTypeIds = StaffType::whereIn('title', ['Admin', 'Moderator'])
            ->pluck('id')
            ->all();

        $isAdminOrModerator = $organization
            ->staff()
            ->whereIn('staff_type_id', $staffTypeIds)
            ->where('user_id', $user->id)
            ->count();
        return $isAdminOrModerator
            ? Response::allow()
            : Response::deny(__('string.policy.organization_add_staff_access'));
    }

}
