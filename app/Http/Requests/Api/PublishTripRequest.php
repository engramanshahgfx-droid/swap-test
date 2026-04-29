<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PublishTripRequest extends FormRequest
{
    private function validateLoField(string $attribute, mixed $value, \Closure $fail): void
    {
        if (is_string($value)) {
            if (mb_strlen($value) > 5000) {
                $fail('The ' . $attribute . ' field is too long.');
            }

            return;
        }

        if (!is_array($value)) {
            $fail('The ' . $attribute . ' field must be a string or list.');
            return;
        }

        foreach ($value as $index => $item) {
            if (!is_array($item)) {
                $fail('Each item in ' . $attribute . ' must be an object.');
                return;
            }

            $time = $item['time'] ?? null;
            $type = $item['type'] ?? ($item['string'] ?? null);

            if (!is_string($time) || !preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $time)) {
                $fail('The ' . $attribute . '.' . $index . '.time must use HH:MM format.');
                return;
            }

            if (!is_string($type) || trim($type) === '') {
                $fail('The ' . $attribute . '.' . $index . '.type (or .string) is required.');
                return;
            }
        }
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'flight_number' => 'nullable|string|max:20',
            'departure' => 'required|string|min:2|max:3',
            'arrival' => 'required|string|min:2|max:3',
            'date' => 'required|date|after_or_equal:today',
            'arrival_date' => 'nullable|date|after_or_equal:date',
            'position' => 'nullable|string|in:Captain,First Officer,Purser,Flight Attendant',
            'notes' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
            'legs' => 'nullable|integer|min:1',
            'fly_type' => 'nullable|string|max:50',
            'departure_time' => 'nullable|string|max:50',
            'arrival_time' => 'nullable|string|max:50',
            'report_time' => 'nullable|string|max:50',
            'offer_lo' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $this->validateLoField($attribute, $value, $fail);
                },
            ],
            'ask_lo' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $this->validateLoField($attribute, $value, $fail);
                },
            ],
            'details' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:5120',
            'image_path' => 'nullable|image|max:5120',
        ];
    }

    protected function prepareForValidation()
    {
        // Merge JSON input directly
        if ($this->isJson()) {
            $this->merge($this->json()->all());
        }

        $arrivalDate = $this->input('arrival_date');
        if ($arrivalDate === null || (is_string($arrivalDate) && trim($arrivalDate) === '')) {
            $arrivalDate = $this->input('arrivalDate', $this->input('arrival date'));
        }

        $flightNumber = $this->input('flight_number');
        if ($flightNumber === null || (is_string($flightNumber) && trim($flightNumber) === '')) {
            $flightNumber = $this->input('flightNumber', $this->input('flight number'));
        }

        $date = $this->input('date');
        if ($date === null || (is_string($date) && trim($date) === '')) {
            $date = $this->input('departure_date', $this->input('departureDate', $this->input('departure date')));
        }

        $this->merge([
            'arrival_date' => $arrivalDate,
            'date' => $date,
            'flight_number' => $flightNumber,
            'departure_time' => $this->input('departure_time', $this->input('departureTime', $this->input('departure time'))),
            'arrival_time' => $this->input('arrival_time', $this->input('arrivalTime', $this->input('arrival time'))),
        ]);
    }
}
