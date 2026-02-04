<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OpenDisputeRequest extends FormRequest
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
            'reason' => 'required|string|max:1000',
            'reason_code' => 'nullable|string|max:50'
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Dispute reason is required',
            'reason.string' => 'Reason must be text',
            'reason.max' => 'Reason cannot exceed 1000 characters',
            'reason_code.max' => 'Reason code cannot exceed 50 characters'
        ];
    }
}