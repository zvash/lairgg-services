<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'string|filled|min:3',
            'bio' => 'string',
            'logo' => 'mimes:jpeg,jpg,png,webp',
            'cover' => 'mimes:jpeg,jpg,png,webp',
            'game_id' => 'int|exists:games,id',
            'links' => 'array|nullable',
            'links.*' => [
                'filled',
                function ($attribute, $value, $fail) {
                    if (
                        ! filter_var($value, FILTER_VALIDATE_EMAIL) &&
                        ! filter_var($value, FILTER_VALIDATE_DOMAIN)
                    ) {
                        $fail('The ' . $attribute . ' is not a valid email address or URL');
                    }
                },
            ]
        ];
    }
}
