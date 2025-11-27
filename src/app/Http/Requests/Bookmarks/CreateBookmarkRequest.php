<?php

declare(strict_types=1);

namespace App\Http\Requests\Bookmarks;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookmarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return $this->user() !== null;
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_id' => [
                'required',
                'integer',
                'exists:resources,id'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'resource_id.required' => 'El ID del recurso es obligatorio.',
            'resource_id.exists' => 'El recurso no existe.',
        ];
    }
}