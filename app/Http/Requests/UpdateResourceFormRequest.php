<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\GithubIdRule;
use App\Rules\RoleStudentRule;

class UpdateResourceFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $resource = $this->route('resource');
        $githubId = $this->input('github_id');

        $isOwner = $resource && $githubId == $resource->github_id;
        $userRole = Role::where('github_id', $githubId)->value('role');
        $isAdmin = in_array($userRole, ['superadmin', 'admin']);

        return $isOwner || $isAdmin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'github_id' => [new \App\Rules\GithubIdRule()],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'min:10', 'max:1000'],
            'url' => ['required', 'url'],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['string', 'distinct', 'exists:tags,name']
        ];
    }
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        // Filtramos para no tener que utilizar github_id
        return array_diff_key($validated, ['github_id' => true]);
    }
    public function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }

        parent::failedValidation($validator);
    }
}


