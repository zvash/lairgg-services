<?php

namespace App\Repositories;


use App\Staff;
use App\Organization;
use App\StaffType;
use App\User;
use Illuminate\Http\Request;


class OrganizationRepository extends BaseRepository
{
    protected $modelClass = Organization::class;

    /**
     * Retrieves all the organizations that
     * the given user is a staff member
     *
     * @param int $userId
     * @return null
     */
    public function allByUserId(int $userId)
    {
        $organizationIdsForUser = Staff::where('user_id', $userId)
            ->pluck('organization_id')
            ->toArray();
        if ($organizationIdsForUser) {
            return Organization::whereIn('id', $organizationIdsForUser)->with(['staff', 'links'])->get();
        }
        return null;
    }

    /**
     * Creates a new organization
     *
     * @param Request $request
     * @return mixed
     */
    public function createOrganizationWithRequest(Request $request)
    {
        $inputs = array_filter($request->all(), function ($key) {
            return in_array($key, ['title', 'slug', 'bio', 'timezone', 'status']);
        }, ARRAY_FILTER_USE_KEY);
        $inputs['logo'] = $this->saveImageFromRequest($request, 'logo', 'organizations/logos');
        $inputs['cover'] = $this->saveImageFromRequest($request, 'cover', 'organizations/covers');
        return Organization::create($inputs);
    }

    /**
     * Updates an existing organization
     *
     * @param Request $request
     * @param Organization $organization
     * @return Organization
     */
    public function editOrganizationWithRequest(Request $request, Organization $organization)
    {
        $inputs = array_filter($request->all(), function ($key) {
            return in_array($key, ['title', 'slug', 'bio', 'timezone', 'status']);
        }, ARRAY_FILTER_USE_KEY);
        $logo = $this->saveImageFromRequest($request, 'logo', 'organizations/logos');
        $cover = $this->saveImageFromRequest($request, 'cover', 'organizations/covers');
        if ($logo) {
            $inputs['logo'] = $logo;
        }
        if ($cover) {
            $inputs['cover'] = $cover;
        }
        foreach ($inputs as $key => $value) {
            $organization->setAttribute($key, $value);
        }
        $organization->save();
        return $organization;
    }

    /**
     * Add staff to organization
     *
     * @param Organization $organization
     * @param User $user
     * @param string $staffTypeTitle
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    public function addStaff(Organization $organization, User $user, string $staffTypeTitle)
    {
        $staffTypeId = StaffType::where('title', $staffTypeTitle)->first()->id;
        if (!$staffTypeId) {
            return null;
        }
        $staff = $organization->staff()
            ->where('user_id', $user->id)
            ->first();
        if ($staff) {
            $staff->staff_type_id = $staffTypeId;
            $staff->save();
        } else {
            $staff = Staff::create([
                'user_id' => $user->id,
                'staff_type_id' => $staffTypeId,
                'organization_id' => $organization->id,
                'owner' => 0
            ]);
        }
        return $staff;
    }
}