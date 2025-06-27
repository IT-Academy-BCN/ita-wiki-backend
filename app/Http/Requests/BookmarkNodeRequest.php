<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\NodeIdRule;
use App\Rules\RoleNodeStudentRule;
use Illuminate\Foundation\Http\FormRequest;


class BookmarkNodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->route('node_id')) {
            $this->merge(['node_id' => $this->route('node_id')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'node_id' => 
            [
                new NodeIdRule(),          // Validate node_id exists in roles_node
                new RoleNodeStudentRule(), // Ensure role is "student" for node_id
            ],
        ];

        if ($this->isMethod('post') || $this->isMethod('delete')) {
            $rules['resource_node_id'] = 'required|integer|exists:resources_node,id';
        }

        return $rules;

        
    }
}
