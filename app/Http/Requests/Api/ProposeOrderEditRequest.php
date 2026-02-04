<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProposeOrderEditRequest extends FormRequest
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
            'new_price' => 'required|numeric|min:0',
            'details' => 'nullable|string|max:1000',
            'reason' => 'required|string|max:500'
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'new_price.required' => 'New price is required',
            'new_price.numeric' => 'Price must be a number',
            'new_price.min' => 'Price must be at least 0',
            'reason.required' => 'Reason for edit is required',
            'reason.max' => 'Reason cannot exceed 500 characters',
            'details.max' => 'Details cannot exceed 1000 characters'
        ];
    }
}