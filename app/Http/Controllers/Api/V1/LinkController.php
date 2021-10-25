<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\LinkType;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function types(Request $request)
    {
        return $this->success(LinkType::all());
    }
}
