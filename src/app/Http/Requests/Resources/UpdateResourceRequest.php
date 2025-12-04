<?php

declare(strict_types=1);

namespace App\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResourceRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        //return $this->user() !== null;
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
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'min:10', 'max:1000'],
            'url' => ['required', 'url'],
            'category' => ['required', 'string', 'in:Node,React,Angular,JavaScript,Java,Fullstack PHP,Data Science,BBDD'],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['string', 'distinct', Rule::exists('tags', 'name')],
            'type' => ['required', 'string', 'in:Video,Cursos,Blog']
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.min' => 'El título debe tener al menos 5 caracteres.',
            'title.max' => 'El título no debe exceder los 255 caracteres.',
            'description.min' => 'La descripción debe tener al menos 10 caracteres.',
            'description.max' => 'La descripción no debe exceder los 1000 caracteres.',
            'url.required' => 'La URL es obligatoria.',
            'url.url' => 'La URL debe ser válida.',
            'category.required' => 'La categoría es obligatoria.',
            'category.in' => 'La categoría seleccionada no es válida.',
            'tags.max' => 'No puedes seleccionar más de 5 tags.',
            'tags.*.exists' => 'Uno o más tags no existen.',
            'type.required' => 'El tipo es obligatorio.',
            'type.in' => 'El tipo seleccionado no es válido.',
        ];
    }
}


