<?php


declare (strict_types= 1);

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
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
            'role' => ['required', 'string', 'in:superadmin,mentor,admin,student'],
        ];
    }   

    public function messages(): array
    {
        return [
            'role.required' => 'The role field is required.',
            'role.string' => 'The role field must be a string.',
            'role.in' => 'The selected role is invalid.',
        ];
    }
}
