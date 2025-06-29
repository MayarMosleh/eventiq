<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('companies')->ignore($this->route('company')),
            ],
            'description' => 'sometimes|required|string',
            'company_image' => 'sometimes|image|mimes:png,jpg,jpeg|max:2048',
        ];
    }
}
