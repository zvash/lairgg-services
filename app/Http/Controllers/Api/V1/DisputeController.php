<?php

namespace App\Http\Controllers\Api\V1;

use App\Dispute;
use Illuminate\Http\Request;
use App\Enums\HttpStatusCode;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Repositories\DisputeRepository;
use App\Traits\Responses\ResponseMaker;
use App\Traits\Responses\ValidityChecker;

class DisputeController extends Controller
{
    use ResponseMaker;
    use ValidityChecker;

    /**
     * @param Request $request
     * @param Dispute $dispute
     * @param DisputeRepository $disputeRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function close(Request $request, Dispute $dispute, DisputeRepository $disputeRepository)
    {
        if (!$dispute) {
            return $this->failNotFound();
        }

        $gate = Gate::inspect('closeDispute', $dispute);
        if (!$gate->allowed()) {
            return $this->failMessage($gate->message(), HttpStatusCode::UNAUTHORIZED);
        }

        $dispute = $disputeRepository->close($dispute);
        return $this->success($dispute);
    }
}
