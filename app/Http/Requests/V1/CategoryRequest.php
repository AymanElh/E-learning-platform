<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
            'name' => 'required|string|unique:categories,name|max:255',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'This Category name is already exist',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'parent_id.integer' => 'The parent category ID must be a valid integer.',
        ];
    }
}
