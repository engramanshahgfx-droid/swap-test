<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripPreferencesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'prefer_destinations' => 'nullable|array',
            'exclude_destinations' => 'nullable|array',
            'prefer_flight_types' => 'nullable|array',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->isJson()) {
            $this->merge($this->json()->all());
        }
    }
}
