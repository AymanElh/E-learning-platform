<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class VideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Will be handled by controller middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ];

        // If this is a POST request (creating new video), require the video file
        if ($this->isMethod('post')) {
            $rules['video'] = 'required|file|mimes:mp4,avi,mov,wmv|max:1048576'; // 1GB max
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            // For updates, video is optional
            $rules['video'] = 'nullable|file|mimes:mp4,avi,mov,wmv|max:1048576';
        }

        return $rules;
    }
}
