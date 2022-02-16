<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShareGemRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'balance_id' => 'required|int|exists:team_balances,id',
            'slices' => 'required|array|filled',
            'slices.*.user_id' => 'required|exists:users,id',
            'slices.*.gems' => 'required|int|min:0'
        ];
    }
}
