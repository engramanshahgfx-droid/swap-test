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
            'mentioned_trip_id' => 'nullable|exists:published_trips,id',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->isJson()) {
            $this->merge($this->json()->all());
        }

        $message = $this->input('message') ?? $this->input('text') ?? $this->input('body');
        $targetUser = $this->input('receiver_id') ?? $this->input('recipient_id') ?? $this->input('user_id');

        $updates = [];
        if ($message !== null && !$this->has('message')) {
            $updates['message'] = $message;
        }
        if ($targetUser !== null && !$this->has('receiver_id')) {
            $updates['receiver_id'] = $targetUser;
        }
        if ($targetUser !== null && !$this->has('recipient_id')) {
            $updates['recipient_id'] = $targetUser;
        }

        if (!empty($updates)) {
            $this->merge($updates);
        }
    }
}
