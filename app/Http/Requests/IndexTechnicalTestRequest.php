<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexTechnicalTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pendiente middleware para alumno y mentor
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'language' => 'nullable|string|in:PHP,JavaScript,Java,React,TypeScript,Python,SQL',
            'description' => 'nullable|string|max:1000',
            'tag' => 'nullable|array|max:50',
            'date_from' => 'nullable|date|date_format:d-m-Y',
            'date_to' => 'nullable|date|date_format:d-m-Y|after_or_equal:date_from',
        ];
    }

    public function messages(): array
    {
        return [
            'language.in' => 'El lenguaje seleccionado no es válido.',
            'description.max' => 'El campo descripción no debe exceder los 1000 caracteres.',
            'date_from.date' => 'El campo fecha desde debe ser una fecha válida.',
            'date_from.date_format' => 'La fecha debe estar en formato dd-mm-yyyy (ejemplo: 08-07-2025).',
            'date_to.date' => 'El campo fecha hasta debe ser una fecha válida.',
            'date_to.date_format' => 'La fecha debe estar en formato dd-mm-yyyy (ejemplo: 08-07-2025).',
            'date_to.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
        ];
    }

}