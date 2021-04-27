<?php

namespace App\Http\Controllers;

use App\Processors\ProcessMessageHelper;
use App\Traits\Responses\ResponseMaker;

class QueueHandlerController extends Controller
{
    use ResponseMaker;
    public function __invoke()
    {
        $helper = new ProcessMessageHelper();
        if ($helper->handle()) {
            return $this->success(['status' => 'ok']);
        }
        return $this->failMessage('failed', 400);
    }
}
