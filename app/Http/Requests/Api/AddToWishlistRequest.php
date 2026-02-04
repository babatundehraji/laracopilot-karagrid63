<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddToWishlistRequest extends FormRequest
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
            'service_id' => 'required|exists:services,id'
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'service_id.required' => 'Service ID is required',
            'service_id.exists' => 'Invalid service selected'
        ];
    }
}