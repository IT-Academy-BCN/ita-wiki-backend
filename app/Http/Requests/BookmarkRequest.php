<?php

declare (strict_types= 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Role;

class BookmarkRequest extends FormRequest
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
        $rules = [
            'github_id' => [
                'required',
                'integer',
                'min:1',
                'exists:roles,github_id',
                function ($attribute, $value, $fail) {
                    $role = Role::where('github_id', $value)->first();
                    if (!$role || $role->role !== 'student') {
                        $fail('The github_id must belong to a student role.');
                    }
                },
            ],
        ];

        if ($this->isMethod('post')) {
            $rules['resource_id'] = 'required|integer|exists:resources,id';
        }

        return $rules;
    }
}
