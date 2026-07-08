<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrivacySettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hide_employee_id' => 'nullable|boolean',
            'show_online_status' => 'nullable|boolean',
            'allow_messages_from' => 'nullable|string|in:everyone,only_swaps,nobody',
        ];
    }
}
