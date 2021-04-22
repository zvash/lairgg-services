<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUser;
use App\Http\Resources\UserResource;
use App\Traits\Responses\ResponseMaker;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;

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
     * Dispatch user jobs and events.
     *
     * @param  \App\User  $user
     * @return \App\User
     */
    protected function dispatchUserJobs(User $user)
    {
        event(new Registered($user));


        return $user;
    }
}
