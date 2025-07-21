<?php

namespace App\Http\Requests\V1\Course;

use Illuminate\Foundation\Http\FormRequest;

class LessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lesson_type' => 'required|in:video,article,quiz,assignment',
            'order_index' => 'nullable|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_free_preview' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Lesson title is required',
            'title.max' => 'Lesson title cannot exceed 255 characters',
            'lesson_type.required' => 'Lesson type is required',
            'lesson_type.in' => 'Lesson type must be: video, article, quiz, or assignment',
            'order_index.integer' => 'Order index must be a valid number',
            'order_index.min' => 'Order index must be at least 1',
            'duration_minutes.integer' => 'Duration must be a valid number',
            'duration_minutes.min' => 'Duration must be at least 1 minute',
        ];
    }
}
