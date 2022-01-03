<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetPlayScoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'scores' => 'required|array|min:2',
            'scores.*.party_id' => 'required|exists:parties,id',
            'scores.*.is_winner' => 'required|boolean',
            'scores.*.score' => 'int',
            'scores.*.is_forfeit' => 'required|boolean',
            'map_id' => 'int|min:0|exists:maps,id',
            'screenshot' => 'mimes:jpeg,jpg,png',
        ];
    }
}
