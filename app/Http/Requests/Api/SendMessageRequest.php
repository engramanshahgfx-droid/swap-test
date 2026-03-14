<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'recipient_id' => 'required_without_all:conversation_id,receiver_id|exists:users,id',
            'receiver_id' => 'required_without_all:conversation_id,recipient_id|exists:users,id',
            'conversation_id' => 'required_without_all:recipient_id,receiver_id|exists:conversations,id',
            'message' => 'required|string|max:1000',
            'message_type' => 'nullable|string|in:text,image,file,system',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->isJson()) {
            $this->merge($this->json()->all());
        }
    }
}
