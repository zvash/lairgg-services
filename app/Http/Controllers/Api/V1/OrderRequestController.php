<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class OrderRequestController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->success(
            $request->user()->orderRequests()->with('requestable')->orderBy('id', 'DESC')->paginate(10)
        );
    }
}
