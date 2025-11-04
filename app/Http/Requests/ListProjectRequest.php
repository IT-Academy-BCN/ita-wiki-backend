<?php

declare (strict_types= 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListProjectRequest extends FormRequest
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
            'time_duration' => 'required|string|max:255',
            'language_backend' => 'required|string|max:255',
            'language_frontend' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => "The title field is required.",
            'time_duration.required' => "The time duration field is required.",
            'time_duration.string' => "The time duration must be a string.",
            'language_backend.required' => "The backend language field is required.",
            'language_frontend.required' => "The frontend language field is required.",
        ];
    }
}
