<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:2000'
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Message is required',
            'message.string' => 'Message must be text',
            'message.max' => 'Message cannot exceed 2000 characters'
        ];
    }
}