<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\LanguageEnum;

class StoreTechnicalTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Por ahora permitimos a todos, después agregaremos autorización
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'language' => 'required|string|in:'. implode(',', LanguageEnum::values()),
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array|max:5',
            'tags.*' => 'string|max:50',
            'file' => 'nullable|file|mimes:pdf|max:10240', 
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.min' => 'El título debe tener al menos 5 caracteres.',
            'title.max' => 'El título no puede exceder 255 caracteres.',
            'language.required' => 'El lenguaje es obligatorio.',
            'language.in' => 'El lenguaje seleccionado no es válido.',
            'description.max' => 'La descripción no puede exceder 1000 caracteres.',
            'tags.max' => 'No puedes agregar más de 5 tags.',
            'file.file' => 'El archivo debe ser válido.',
            'file.mimes' => 'El archivo debe ser un PDF.',
            'file.max' => 'El archivo no puede exceder 10MB.',
        ];
    }
}