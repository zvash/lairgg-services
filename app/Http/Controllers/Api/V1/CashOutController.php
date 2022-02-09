<?php

namespace App\Http\Controllers\Api\V1;

use App\CashOut;
use App\Enums\HttpStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCashOutRequest;
use App\Repositories\CashOutRepository;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class CashOutController extends Controller
{
    use ResponseMaker;

    /**
     * @param StoreCashOutRequest $request
     * @param CashOutRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function createCashOut(StoreCashOutRequest $request, CashOutRepository $repository)
    {
        $validated = $request->validated();
        return $this->success($repository->createPendingCashOut($request->user(), $validated));
    }

    /**
     * @param Request $request
     * @param CashOut $cashOut
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function get(Request $request, CashOut $cashOut)
    {
        $gate = Gate::inspect('get', $cashOut);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::FORBIDDEN);
        }
        return $this->success($cashOut);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getExchangeRate(Request $request)
    {
        return $this->success(['gem_to_cash_rate' => config('cash_out.point_to_cash_rate')]);
    }
}
