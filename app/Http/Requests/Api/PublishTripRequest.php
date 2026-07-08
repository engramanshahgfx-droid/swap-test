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

            $timeIsValid = false;
            if (is_string($time)) {
                $trimmedTime = trim($time);
                // Accept H:MM or HH:MM with any non-negative hour count, or any non-negative number
                if (preg_match('/^\d+:[0-5]\d$/', $trimmedTime) || preg_match('/^\d+(?:\.\d+)?$/', $trimmedTime)) {
                    $timeIsValid = true;
                }
            } elseif (is_int($time) || is_float($time)) {
                // Accept any non-negative number
                if ($time >= 0) {
                    $timeIsValid = true;
                }
            }

            if (!$timeIsValid) {
                $fail('The ' . $attribute . '.' . $index . '.time must be a number or use H:MM format.');
                return;
            }

            if (!is_string($type) || trim($type) === '') {
                $fail('The ' . $attribute . '.' . $index . '.type (or .string) is required.');
                return;
            }
        }
    }

    private function isUpdateRequest(): bool
    {
        return in_array($this->method(), ['PUT', 'PATCH'], true);
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = $this->isUpdateRequest();

        return [
            'flight_number' => 'nullable|string|max:20',
            'departure' => $isUpdate ? 'nullable|string|min:2|max:3' : 'required|string|min:2|max:3',
            'arrival' => $isUpdate ? 'nullable|string|min:2|max:3' : 'required|string|min:2|max:3',
            'date' => 'nullable|date|after_or_equal:today',
            'departure_date' => $isUpdate ? 'nullable|date|after_or_equal:today' : 'required_without:date|date|after_or_equal:today',
            'arrival_date' => 'nullable|date',
            'position' => 'nullable|string|in:Captain,First Officer,Purser,Flight Attendant',
            'notes' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
            'legs' => 'nullable|integer|min:1',
            'fly_type' => 'nullable|string|max:50',
            'departure_time' => 'nullable|string|max:50',
            'arrival_time' => 'nullable|string|max:50',
            'report_time' => 'nullable|string|max:50',
                'is_urgent' => 'nullable|boolean',
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
            'swap_window' => 'nullable|string|in:same_day,day_before,any',
            'image' => 'nullable|image|max:5120',
            'image_path' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->hasFile($attribute)) {
                        $file = $this->file($attribute);
                        if (!$file->isValid()) {
                            $fail('The ' . $attribute . ' file upload is invalid.');
                            return;
                        }

                        if (!in_array($file->extension(), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true)) {
                            $fail('The ' . $attribute . ' must be a valid image file.');
                            return;
                        }

                        if ($file->getSize() > 5120 * 1024) {
                            $fail('The ' . $attribute . ' must not be greater than 5120 kilobytes.');
                        }

                        return;
                    }

                    if ($value === null || (is_string($value) && trim($value) === '')) {
                        return;
                    }

                    if (!is_string($value)) {
                        $fail('The ' . $attribute . ' must be a valid string path or image file.');
                    }
                },
            ],
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

        $departureDate = $this->input('departure_date');
        if ($departureDate === null || (is_string($departureDate) && trim($departureDate) === '')) {
            $departureDate = $this->input('departureDate', $this->input('departure date', $this->input('date')));
        }

        $date = $this->input('date');
        if ($date === null || (is_string($date) && trim($date) === '')) {
            $date = $departureDate;
        }

        if ($departureDate === null || (is_string($departureDate) && trim($departureDate) === '')) {
            $departureDate = $date;
        }

        $imagePath = $this->input('image_path');
        if ($imagePath === null || (is_string($imagePath) && trim($imagePath) === '')) {
            $imagePath = $this->input('imagePath', $this->input('image path'));
        }

        $offerLo = $this->input('offer_lo');
        if ($offerLo === null || (is_string($offerLo) && trim($offerLo) === '')) {
            $offerLo = $this->input('offerLo', $this->input('offer lo'));
        }
        if (is_string($offerLo) && $offerLo !== '') {
            $decoded = json_decode($offerLo, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $offerLo = $decoded;
            }
        }

        $askLo = $this->input('ask_lo');
        if ($askLo === null || (is_string($askLo) && trim($askLo) === '')) {
            $askLo = $this->input('askLo', $this->input('ask lo'));
        }
        if (is_string($askLo) && $askLo !== '') {
            $decoded = json_decode($askLo, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $askLo = $decoded;
            }
        }

        $this->merge([
            'arrival_date' => $arrivalDate,
            'departure_date' => $departureDate,
            'date' => $date,
            'flight_number' => $flightNumber,
            'departure_time' => $this->input('departure_time', $this->input('departureTime', $this->input('departure time'))),
            'arrival_time' => $this->input('arrival_time', $this->input('arrivalTime', $this->input('arrival time'))),
            'image_path' => $imagePath,
            'offer_lo' => $offerLo,
            'ask_lo' => $askLo,
            'swap_window' => $this->input('swap_window', $this->input('swapWindow', $this->input('swap window'))),
        ]);
    }
}
