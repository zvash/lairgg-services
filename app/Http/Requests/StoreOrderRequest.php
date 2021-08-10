<?php

namespace App\Http\Requests;

use App\Repositories\CountryRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $countryRepository = new CountryRepository();
        return [
            'product_id' => 'required|exists:products,id',
            'name' => 'required|regex:/^[\pL\pM\s-]+$/u|max:100',
            'address' => 'required|filled',
            'city' => 'required|filled',
            'state' => 'required|filled',
            'country' => 'required|filled|in:' . $countryRepository->getAllNameVariationsAsString(),
            'postal_code' => 'required|filled',
        ];
    }
}
