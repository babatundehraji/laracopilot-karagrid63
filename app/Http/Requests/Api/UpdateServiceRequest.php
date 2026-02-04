<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'nullable|in:fixed,hourly,daily,negotiable',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_remote' => 'nullable|boolean',
            'is_onsite' => 'nullable|boolean',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'url|max:500',
            
            // Weekly availability (optional update)
            'availability' => 'nullable|array',
            'availability.*.day_of_week' => 'required|integer|min:0|max:6',
            'availability.*.start_time' => 'required|date_format:H:i',
            'availability.*.end_time' => 'required|date_format:H:i|after:availability.*.start_time',
            'availability.*.is_active' => 'nullable|boolean'
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'title.max' => 'Service title cannot exceed 255 characters',
            'category_id.exists' => 'Invalid category selected',
            'subcategory_id.exists' => 'Invalid subcategory selected',
            'price.min' => 'Price must be at least 0',
            'price_type.in' => 'Price type must be fixed, hourly, daily, or negotiable',
            'availability.*.day_of_week.min' => 'Day of week must be between 0 (Sunday) and 6 (Saturday)',
            'availability.*.day_of_week.max' => 'Day of week must be between 0 (Sunday) and 6 (Saturday)',
            'availability.*.start_time.date_format' => 'Start time must be in HH:MM format',
            'availability.*.end_time.date_format' => 'End time must be in HH:MM format',
            'availability.*.end_time.after' => 'End time must be after start time'
        ];
    }
}