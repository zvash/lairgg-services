<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetIdentifiersRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'email:rfc,dns|unique:users,email',
            'username' => 'regex:/^[\.\w\-]{4,50}$/i|unique:users,username',
        ];
    }
}
