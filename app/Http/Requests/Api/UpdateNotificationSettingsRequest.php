<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'swap_requests' => 'nullable|boolean',
            'messages' => 'nullable|boolean',
            'system_updates' => 'nullable|boolean',
        ];
    }
}
