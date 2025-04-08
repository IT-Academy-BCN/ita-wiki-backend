<?php

declare (strict_types= 1);

namespace App\Http\Requests;

use App\Rules\RoleAnonymousRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
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
            'github_id' => [
                'required',
                'exists:roles,github_id'
            ],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'url' => ['required', 'url'],
            'category' => ['required', 'string', 'in:Node,React,Angular,JavaScript,Java,Fullstack PHP,Data Science,BBDD'],
            'theme' => ['required', 'string', 'in:All,Components,UseState & UseEffect,Eventos,Renderizado condicional,Listas,Estilos,Debugging,React Router'],
            'type' =>['required', 'string', 'in:Video,Cursos,Blog']
        ];
       
    }  
}
