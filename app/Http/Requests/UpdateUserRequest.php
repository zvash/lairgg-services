<?php

namespace App\Http\Requests;

use App\Repositories\CountryRepository;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'avatar' => 'mimes:jpeg,jpg,png,webp',
            'cover' => 'mimes:jpeg,jpg,png,webp',
            'bio' => 'string',
            'dob' => 'date|date_format:Y-m-d',
            'phone' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'address' => 'string',
            'state' => 'string',
            'city' => 'string',
            'country' => 'string|in:' . (new CountryRepository())->getAllNameVariationsAsString(),
            'postal_code' => 'digits_between:5,10',
            'timezone' => 'string',
            'gender_id' => 'int|exists:genders,id',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $country = $this->country;
        if ($country) {
            $alpha2Name = (new CountryRepository())->getAlpha2($country);
            $this->merge([
                'country' => $alpha2Name,
            ]);
        }

    }
}
