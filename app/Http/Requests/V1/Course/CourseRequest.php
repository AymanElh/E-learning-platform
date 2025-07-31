<?php

namespace App\Http\Requests\V1\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        // debugging
        $routeParam = $this->route('course');
        \Log::info("Route parameter: ", ['param' => $routeParam]);

        $courseId = null;

        if(is_object($routeParam)) {
            $courseId = $routeParam->id;
            \Log::info("Object");
        } else if(is_numeric($routeParam)) {
            $courseId = $routeParam;
            \Log::info("numeric");
        } else {
            $courseId = $this->route()->parameter('course');
        }

        \Log::info("Course ID for validation: ", ['id' => $courseId]);

//        $courseId = $this->route('course') ? $this->route('course')->id ?? $this->route('course') : null;
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')->ignore($courseId)
            ],
            'duration' => 'nullable|integer|min:1',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'status' => 'nullable|in:open,closed',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'thumbnail_url' => 'nullable|string|max:255|url',
            'instructor_id' => 'nullable|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
