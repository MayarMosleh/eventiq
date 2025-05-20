<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
             'name'=>'string|max:100|required' ,'email'=>'email|required|unique:users,email','email_verified_at'=>'nullable|email|unique:users,email' ,'password'=>'required|string|confirmed','role'=>'required|string'
        ];
    }
}
