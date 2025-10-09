<?php

declare (strict_types= 1);

namespace App\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;

class ShowResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'search.max' => 'La búsqueda es demasiado larga (máximo 255 caracteres).',
            'search.regex' => 'La búsqueda solo puede contener letras, números y espacios.',
        ];
    }
}
