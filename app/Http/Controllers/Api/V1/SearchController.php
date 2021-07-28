<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\SearchRepository;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @param SearchRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function search(Request $request, SearchRepository $repository)
    {
        $query = '';
        if ($request->has('q')) {
            $query = $request->get('q');
        }
        return $this->success($repository->search($query));
    }
}
