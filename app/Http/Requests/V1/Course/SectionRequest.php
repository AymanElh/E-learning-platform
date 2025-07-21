<?php

namespace App\Http\Requests\V1\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
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
//        dd($this->data());
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:sections,title' . $this->route('section')
            ],
            'description' => ['nullable', 'string'],
            'order_index' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['nullable','boolean'],
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
            'title.required' => 'Section title is required',
            'title.unique' => 'Section title of the course should be unique',
            'title.max' => 'Section title cannot exceed 255 characters',
            'order_index.integer' => 'Order index must be a valid number',
            'order_index.min' => 'Order index must be at least 1',
        ];
    }
}
