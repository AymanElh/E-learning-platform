<?php

namespace App\Http\Requests\V1\Course;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
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
            'slug' => 'sometimes|string|max:255|unique:courses,slug,' . $this->route('course'),
            'duration' => 'nullable|integer|min:1',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'status' => 'nullable|in:open,closed',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'thumbnail_url' => 'nullable|string|max:255|url',
            'instructor_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
