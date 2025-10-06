<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\LanguageEnum;
use Illuminate\Validation\Rule;

class StoreTechnicalTestRequest extends FormRequest
{
    public function authorize(): bool
    {
       
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:5',
                'max:255'
            ],
            'language' => [
                'required',
                'string',
                Rule::enum(LanguageEnum::class)
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'tags' => [
                'nullable',
                'array'
            ],
            'tags.*' => [
                'string',
                'max:50'
            ],
            'file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:10240'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.min' => 'El título debe tener al menos 5 caracteres.',
            'title.max' => 'El título no debe exceder los 255 caracteres.',
            'language.required' => 'El lenguaje es obligatorio.',
            'language.enum' => 'El lenguaje seleccionado no es válido.',
            'description.max' => 'La descripción no debe exceder los 1000 caracteres.',
            'file.mimes' => 'El archivo debe ser un PDF.',
            'file.max' => 'El archivo no debe exceder los 10MB.',
        ];
    }
}