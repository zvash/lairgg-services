<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\CountryRepository;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    use ResponseMaker;

    /**
     * @param CountryRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function all(CountryRepository $repository)
    {
        return $this->success($repository->getAllAsArray());
    }
}
