<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|filled|min:3',
            'bio' => 'string',
            'logo' => 'mimes:jpeg,jpg,png,webp',
            'cover' => 'mimes:jpeg,jpg,png,webp',
            'game_id' => 'required|int|exists:games,id',
        ];
    }
}
