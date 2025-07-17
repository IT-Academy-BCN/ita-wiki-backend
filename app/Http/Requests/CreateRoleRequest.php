<?php

declare (strict_types= 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
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
            'authorized_github_id' => ['required', 'int', 'gt:0', 'exists:users,github_id'],
            'github_id' => ['required', 'int', 'gt:0', 'unique:users,github_id'],   
            'role' => ['required', 'string', 'in:superadmin,mentor,admin,student'],
        ];
    }   
}
