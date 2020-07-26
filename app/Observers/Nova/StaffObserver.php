<?php

namespace App\Observers\Nova;

use App\Staff;
use App\Traits\Observers\Validator;

class StaffObserver
{
    use Validator;

    /**
     * Handle the staff "creating" event.
     *
     * @param  \App\Staff  $staff
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function creating(Staff $staff)
    {
        $this->validate($staff);
    }

    /**
     * Handle the staff "saving" event.
     *
     * @param  \App\Staff  $staff
     * @return void
     */
    public function saving(Staff $staff)
    {
        if ($staff->owner) {
            Staff::owner()->where([
                ['organization_id', $staff->organization_id],
                ['id', '!=', $staff->id],
            ])->update(['owner' => false]);
        }
    }

    /**
     * Handle the staff "deleted" event.
     *
     * @param  \App\Staff  $staff
     * @return void
     */
    public function deleted(Staff $staff)
    {
        if ($staff->owner) {
            Staff::whereOrganizationId($staff->organization_id)
                ->take(1)->oldest()
                ->update(['owner' => true]);
        }
    }

    /**
     * The data array for validator.
     *
     * @return array
     */
    protected function data()
    {
        $user = Staff::whereUserId($this->resource->user_id)->doesntExist();

        return compact('user');
    }

    /**
     * The rules array for validator.
     *
     * @return array
     */
    protected function rules()
    {
        return ['user' => 'boolean|accepted'];
    }

    /**
     * The messages array for validator.
     *
     * @return array
     */
    protected function messages()
    {
        return ['user.accepted' => __('validation.nova.user.accepted')];
    }
}
