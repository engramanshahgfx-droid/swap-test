<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PublishTripRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'flight_number' => 'required|string|max:20',
            'departure' => 'required|string|size:3',
            'arrival' => 'required|string|size:3',
            'date' => 'required|date|after:today',
            'position' => 'required|string|in:Captain,First Officer,Purser,Flight Attendant',
            'notes' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
            'legs' => 'nullable|integer|min:1',
            'fly_type' => 'nullable|string|max:50',
            'report_time' => 'nullable|string|max:50',
            'offer_lo' => 'nullable|string|max:100',
            'ask_lo' => 'nullable|string|max:100',
            'details' => 'nullable|string|max:1000',
        ];
    }

    protected function prepareForValidation()
    {
        // Merge JSON input directly
        if ($this->isJson()) {
            $this->merge($this->json()->all());
        }
    }
}
