<?php

namespace App\Traits\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidityChecker
{
    /**
     * @param Request $request
     * @param array $rules
     * @return array
     */
    private function validateRules(Request $request, array $rules)
    {
        $validator = Validator::make($request->all(), $rules);
        return [
            $validator->fails(),
            $validator
        ];
    }
}