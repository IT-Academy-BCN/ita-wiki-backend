<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\GithubIdRule;
use App\Rules\RoleStudentRule;

class UpdateResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /*
        $resource = $this->route('resource');
        return $resource && $this->input('github_id') == $resource->github_id;
        */
        // Code commented above returns 500 instead of 403 (?!) but seems to be correct (postman trials)... We probably need a session model that extends Authenticable (github_id and token)
        // (works but also breaks tets)
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
            'github_id' => ['required', 'integer', 'gt:0'],
            // Code below works but is not the way to do it, we should use authorize() above
            /*
            'github_id' => [
                new GithubIdRule(),
                new RoleStudentRule(),
                function ($attribute, $value, $fail) {
                    $resource = $this->route('resource');
                    if (!$resource || $value != $resource->github_id) {
                        $fail('You are not authorized to update this resource.');
                    }
                }
            ],
            */
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


