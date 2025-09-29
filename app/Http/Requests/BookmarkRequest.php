<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'github_id' => 'required|integer',  
        ];

        if ($this->isMethod('post') || $this->isMethod('delete')) {
            $rules['resource_id'] = 'required|integer|exists:resources,id';
        }

        return $rules;
    }
}
