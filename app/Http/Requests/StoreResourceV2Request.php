<?php

declare (strict_types= 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\GithubIdRule;
use App\Rules\RoleStudentRule;

class StoreResourceV2Request extends FormRequest
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
                new GithubIdRule(),
                new RoleStudentRule(), // Comment if you don't want to restrict to students only
            ],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'min:10', 'max:1000'],
            'url' => ['required', 'url'],
            'category' => ['required', 'string', 'in:Node,React,Angular,JavaScript,Java,Fullstack PHP,Data Science,BBDD'],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['integer', 'distinct', Rule::exists('tags', 'id')],
            'type' =>['required', 'string', 'in:Video,Cursos,Blog']
        ];
    }  
}
