<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|string|unique:users',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'country_base' => 'required|string',
            'airline_id' => 'required|exists:airlines,id',
            'plane_type_id' => 'required|exists:plane_types,id',
            'position_id' => 'required|exists:positions,id',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.unique' => 'This employee ID is already registered',
            'phone.unique' => 'This phone number is already registered',
            'email.unique' => 'This email is already registered',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator);
    }

    protected function prepareForValidation()
    {
        if ($this->isJson()) {
            $this->merge($this->json()->all());
        }
    }
}

