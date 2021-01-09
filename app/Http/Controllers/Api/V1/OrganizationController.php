<?php

namespace App\Http\Controllers\Api\V1;

use DateTimeZone;
use App\Organization;
use Illuminate\Http\Request;
use App\Enums\HttpStatusCode;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Repositories\StaffRepository;
use App\Traits\Responses\ResponseMaker;
use App\Traits\Responses\ValidityChecker;
use Illuminate\Support\Facades\Validator;
use App\Repositories\TournamentRepository;
use App\Repositories\OrganizationRepository;

class OrganizationController extends Controller
{
    use ResponseMaker;
    use ValidityChecker;

    /**
     * Creates a new organization and its owner
     *
     * @param Request $request
     * @param OrganizationRepository $organizationRepository
     * @param StaffRepository $staffRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request, OrganizationRepository $organizationRepository, StaffRepository $staffRepository)
    {
        list($failed, $validator) = $this->validateCreateOrganization($request);
        if ($failed) {
            return $this->failValidation($validator->errors());
        }

        $organization = $organizationRepository->createOrganizationWithRequest($request);
        $user = Auth::user();
        $staffRepository->createOrganizationOwner($user, $organization);
        return $this->success($organization);
    }

    /**
     * Updates an existing organization
     *
     * @param Request $request
     * @param int $organizationId
     * @param OrganizationRepository $organizationRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function edit(Request $request, int $organizationId, OrganizationRepository $organizationRepository)
    {
        $organization = Organization::find($organizationId);
        if (!$organization) {
            return $this->failNotFound();
        }
        $gate = Gate::inspect('update', $organization);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        list($failed, $validator) = $this->validateEditOrganization($request, $organization);
        if ($failed) {
            return $this->failValidation($validator->errors());
        }

        $organization = $organizationRepository->editOrganizationWithRequest($request, $organization);
        return $this->success($organization);
    }

    /**
     * Retrieves a list of all organizations
     *
     * @param OrganizationRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function all(OrganizationRepository $repository)
    {
        $user = Auth::user();
        if ($user) {
            $organizations = $repository->allByUserId($user->id);
            if ($organizations) {
                return $this->success($organizations);
            }
            return $this->failNotFound();
        }
        return $this->failNotFound();
    }

    /**
     * Retrieves a list of tournaments for given organization
     * and group them by their started_at column of model
     *
     * @param int $organizationId
     * @param TournamentRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function tournaments(int $organizationId, TournamentRepository $repository)
    {
        $tournaments = $repository->tournamentsByDateForOrganization($organizationId);
        return $this->success($tournaments);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateCreateOrganization(Request $request)
    {
        $timezones = collect(DateTimeZone::listIdentifiers(DateTimeZone::ALL))->map(function ($timezone) {
            return $timezone;
        })->all();
        $rules = [
            'title' => 'required|filled',
            'slug' => 'required|filled|unique:organizations',
            'bio' => 'string',
            'timezone' => 'required|string|in:' . implode(',', $timezones),
            'logo' => 'mimes:jpeg,jpg,png',
            'cover' => 'mimes:jpeg,jpg,png',
            'status' => 'required|boolean'
        ];
        return $this->validateRules($request, $rules);
    }

    /**
     * @param Request $request
     * @param Organization $organization
     * @return array
     */
    private function validateEditOrganization(Request $request, Organization $organization)
    {
        $timezones = collect(DateTimeZone::listIdentifiers(DateTimeZone::ALL))->map(function ($timezone) {
            return $timezone;
        })->all();
        $rules = [
            'title' => 'filled',
            'slug' => [
                'filled',
                Rule::unique('organizations')->ignore($organization)
            ],
            'bio' => 'string',
            'timezone' => 'string|in:' . implode(',', $timezones),
            'logo' => 'mimes:jpeg,jpg,png',
            'cover' => 'mimes:jpeg,jpg,png',
            'status' => 'boolean'
        ];
        return $this->validateRules($request, $rules);
    }


}
