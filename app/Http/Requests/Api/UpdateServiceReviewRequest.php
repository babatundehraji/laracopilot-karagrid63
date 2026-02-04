<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceReviewRequest extends FormRequest
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
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'rating.integer' => 'Rating must be a number',
            'rating.min' => 'Rating must be at least 1',
            'rating.max' => 'Rating must be at most 5',
            'comment.max' => 'Comment cannot exceed 1000 characters'
        ];
    }
}