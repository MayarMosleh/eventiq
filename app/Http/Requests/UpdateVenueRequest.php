<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVenueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'venue_name' =>[
                'required',
                'string',
                Rule::unique('venues')->ignore($this->route('venue')),
                'sometimes|required'
            ],
            'address' => 'string|sometimes|required',
            'capacity' => 'sometimes|required|integer|min:10',
            'venue_price' => 'sometimes|required|numeric|min:0',
        ];
    }
}
