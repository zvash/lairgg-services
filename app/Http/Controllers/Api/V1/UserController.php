<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetIdentifiersRequest;
use App\Http\Requests\StoreUser;
use App\Http\Resources\UserResource;
use App\Traits\Responses\ResponseMaker;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ResponseMaker;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUser $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     *
     */
    public function store(StoreUser $request)
    {
        $validated = $request->validated();

        $resource = new UserResource(
            $this->dispatchUserJobs(User::register($validated))
        );

        return $this->success($resource);
    }

    /**
     * @param SetIdentifiersRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setMissingIdentifiers(SetIdentifiersRequest $request)
    {
        $validated = collect($request->validated())
            ->only(['email', 'username'])
            ->toArray();

        $user = Auth::user();
        try {
            $setKeys = $this->setMissingFields($validated, $user);
            $user->save();
            if (array_key_exists('email', $setKeys)) {
                $this->dispatchUserJobs($user);
            }
            return $this->success(new UserResource($user));
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), $exception->getCode());
        }

    }

    /**
     * Dispatch user jobs and events.
     *
     * @param  \App\User $user
     * @return \App\User
     */
    protected function dispatchUserJobs(User $user)
    {
        event(new Registered($user));


        return $user;
    }

    /**
     * @param array $params
     * @param $user
     * @return array
     * @throws \Exception
     */
    private function setMissingFields(array $params, &$user): array
    {
        $setKeys = [];
        foreach ($params as $key => $value) {
            if (!$user->$key) {
                $user->$key = $value;
                $setKeys[] = $key;
            } else {
                throw new \Exception("This user has already set the $key field", 400);
            }
        }
        return $setKeys;
    }
}
