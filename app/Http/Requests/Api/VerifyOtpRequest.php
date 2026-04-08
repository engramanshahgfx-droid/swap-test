<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|digits:6',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->isJson()) {
            $this->merge($this->json()->all());
        }

        if ($this->has('otp')) {
            $this->merge([
                'otp' => str_pad((string) $this->input('otp'), 6, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
