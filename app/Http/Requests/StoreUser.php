<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUser extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'regex:/^[\pL\pM\s-]+$/u|max:50',
            'last_name' => 'regex:/^[\pL\pM\s-]+$/u|max:50',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'username' => 'required|regex:/^[\.\w\-]{4,50}$/i|unique:users,username',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ];
    }
}
