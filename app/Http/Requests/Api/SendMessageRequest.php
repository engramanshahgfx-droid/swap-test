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
            'recipient_id' => 'required_without_all:conversation_id,receiver_id,user_id,to_user_id,target_id|nullable|exists:users,id',
            'receiver_id' => 'required_without_all:conversation_id,recipient_id,user_id,to_user_id,target_id|nullable|exists:users,id',
            'user_id' => 'nullable|exists:users,id',
            'conversation_id' => 'required_without_all:recipient_id,receiver_id,user_id,to_user_id,target_id|nullable|exists:conversations,id',
            'message' => 'required|string|max:2000',
            'message_type' => 'nullable|string|in:text,image,file,system',
            'mentioned_trip_id' => 'nullable|integer',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->isJson()) {
            $this->merge($this->json()->all());
        }

        $message = $this->input('message') ?? $this->input('text') ?? $this->input('body') ?? $this->input('content') ?? $this->input('msg');
        $targetUser = $this->input('receiver_id') ?? $this->input('recipient_id') ?? $this->input('user_id') ?? $this->input('to_user_id') ?? $this->input('target_id');

        $updates = [];
        if ($message !== null && !$this->has('message')) {
            $updates['message'] = (string) $message;
        }
        if ($targetUser !== null) {
            if (!$this->has('receiver_id')) {
                $updates['receiver_id'] = $targetUser;
            }
            if (!$this->has('recipient_id')) {
                $updates['recipient_id'] = $targetUser;
            }
            if (!$this->has('user_id')) {
                $updates['user_id'] = $targetUser;
            }
        }

        if (!empty($updates)) {
            $this->merge($updates);
        }
    }
}
