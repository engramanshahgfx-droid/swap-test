<?php

namespace App\Http\Requests\Api;

use App\Models\Airport;
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
            'country_base' => 'nullable|string|max:255|required_without:airport_id',
            'airport_id' => [
                'nullable',
                'integer',
                Rule::exists('airports', 'id')->where(fn ($query) => $query->where('is_active', true)),
                'required_without:country_base',
            ],
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
            'country_base.required_without' => 'Base airport is required when airport_id is not provided',
            'airport_id.required_without' => 'Airport is required when country_base is not provided',
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

        if ($this->filled('country_base') && is_string($this->country_base)) {
            $countryBase = trim($this->country_base);

            // Keep existing 3-letter airport code behavior while normalizing case.
            if (strlen($countryBase) === 3) {
                $countryBase = strtoupper($countryBase);
            }

            $this->merge(['country_base' => $countryBase]);
        }

        if (!$this->filled('country_base') && $this->filled('airport_id')) {
            $airport = Airport::query()
                ->where('id', $this->integer('airport_id'))
                ->select(['iata_code', 'name'])
                ->first();

            if ($airport) {
                $this->merge([
                    'country_base' => $airport->iata_code ?: $airport->name,
                ]);
            }
        }
    }
}

