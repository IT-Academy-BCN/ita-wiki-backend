<?php

namespace App\Http\Requests\Exercises;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'order' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'is_completed' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es requerido.',
            'title.min' => 'El título debe tener al menos 3 caracteres.',
            'title.max' => 'El título no debe exceder los 255 caracteres.',
            'description.max' => 'La descripción no debe exceder los 1000 caracteres.',
            'order.min' => 'El orden debe ser al menos 1.',
            'is_completed.boolean' => 'El campo is_completed debe ser verdadero o falso.'
        ];
    }
}
