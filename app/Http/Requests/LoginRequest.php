<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'grant_type' => 'required|string|filled',
            'client_id' => 'required|int|min:1',
            'client_secret' => 'required|string|filled',
            'username' => 'required|string|filled',
            'password' => 'required|string|filled',
            'scope' => 'required|string|filled'
        ];
    }
}
