<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HttpStatusCode;
use App\Play;
use App\Repositories\PlayRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Traits\Responses\ResponseMaker;
use App\Traits\Responses\ValidityChecker;

class PlayController extends Controller
{
    use ResponseMaker;
    use ValidityChecker;

    /**
     * Update the given play with map or screenshot
     *
     * @param Request $request
     * @param Play $play
     * @param PlayRepository $playRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update(Request $request, Play $play, PlayRepository $playRepository)
    {
        if (!$play) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('update', $play);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        list($failed, $validator) = $this->validateUpdatePlay($request);
        if ($failed) {
            return $this->failValidation($validator->errors());
        }
        $user = Auth::user();
        $play = $playRepository->editPlayWithRequest($request, $play, $user);
        return $this->success($play);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateUpdatePlay(Request $request)
    {
        $rules = [
            'map_id' => 'int:exists:maps,id',
            'screenshot' => 'mimes:jpeg,jpg,png',
        ];
        return $this->validateRules($request, $rules);
    }
}