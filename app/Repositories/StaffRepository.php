<?php

namespace App\Repositories;


use App\Organization;
use App\Staff;
use App\StaffType;
use App\User;

class StaffRepository extends BaseRepository
{
    protected $modelClass = Staff::class;

    /**
     * Creates an Admin and an Owner staff for the given organization
     *
     * @param User $user
     * @param Organization $organization
     * @return mixed
     */
    public function createOrganizationOwner(User $user, Organization $organization)
    {
        $staffTypeId = 1;
        $staffType = StaffType::where('title', 'Admin')->first();
        if ($staffType) {
            $staffTypeId = $staffType->id;
        }
        $inputs = [
            'user_id' => $user->id,
            'staff_type_id' => $staffTypeId,
            'organization_id' => $organization->id,
            'owner' => true
        ];
        return Staff::create($inputs);
    }
}