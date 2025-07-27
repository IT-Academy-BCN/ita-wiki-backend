<?php

declare (strict_types= 1);

namespace App\Rules;

use App\Models\OldRole;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RoleStudentRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $role = OldRole::where('github_id', $value)->first();
        if (!$role || $role->role !== 'student') {
            $fail('The github_id must belong to a student role.');
        }
    }
}
