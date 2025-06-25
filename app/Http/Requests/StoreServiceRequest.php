<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'company_events_id' => 'required|exists:company_events,id',
            'service_name' => 'required|string|max:255',
            'service_description' => 'required|string',
            'service_price' => 'required|numeric|min:0',
            'service_quantity' => 'required|integer|min:1',
        ];
    }
}
