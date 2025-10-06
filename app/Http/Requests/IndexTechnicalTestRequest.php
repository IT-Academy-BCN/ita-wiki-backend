<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\LanguageEnum;

class IndexTechnicalTestRequest extends FormRequest
{
    public function authorize(): bool
    {
       
        return $this->user() && $this->user()->can('view technical tests');
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|in:' . implode(',', array_column(LanguageEnum::cases(), 'value')),
            'description' => 'sometimes|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'search.max' => 'El título no debe exceder los 255 caracteres.',            
            'language.in' => 'El lenguaje seleccionado no es válido.',
            'description.max' => 'El campo descripción no debe exceder los 1000 caracteres.',            
        ];
    }
}