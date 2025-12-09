<?php

namespace App\Http\Requests\TechnicalTests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\LanguageEnum;
use App\Enums\DifficultyLevelEnum;
use App\Enums\TechnicalTestStatusEnum;
use Illuminate\Validation\Rule;

class StoreTechnicalTestRequest extends FormRequest
{
    public function authorize(): bool
    {
       
       // return $this->user() !== null;
        return true;
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
            ],
            'difficulty_level' => [
                'nullable',
                'string',
                Rule::enum(DifficultyLevelEnum::class)
            ],
            'duration' => [
                'nullable',
                'integer',
                'min:1',
                'max:480'
            ],
            'state' => [
                'nullable',
                'string',
                Rule::enum(TechnicalTestStatusEnum::class)
            ],
            'exercises' => [
                'nullable',
                'array',
                'max:20'
            ],
            'exercises.*.title' => [
                'required_with:exercises',
                'string',
                'max:255'
            ],
            'exercises.*.description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'exercises.*.is_completed' => [
                'nullable',
                'boolean'
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
            'difficulty_level.enum' => 'El nivel de dificultad seleccionado no es válido.',
            'duration.integer' => 'La duración debe ser un número entero.',
            'duration.min' => 'La duración debe ser al menos 1 minuto.',
            'duration.max' => 'La duración no debe exceder los 480 minutos (8 horas).',
            'state.enum' => 'El estado seleccionado no es válido.',
            'exercises.array' => 'Los ejercicios deben ser un array.',
            'exercises.max' => 'No se pueden agregar más de 20 ejercicios.',
            'exercises.*.title.required_with' => 'El título del ejercicio es obligatorio.',
            'exercises.*.title.string' => 'El título del ejercicio debe ser una cadena de texto.',
            'exercises.*.title.max' => 'El título del ejercicio no debe exceder los 255 caracteres.',
            'exercises.*.description.string' => 'La descripción del ejercicio debe ser una cadena de texto.',
            'exercises.*.description.max' => 'La descripción del ejercicio no debe exceder los 1000 caracteres.',
            'exercises.*.is_completed.boolean' => 'El campo completado debe ser verdadero o falso.',
        ];
    }
}