<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CommaSeparatedRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[\w\s\-]+(,\s*[\w\s\-]+)*$/', $value)) {
            $fail("The $attribute must be a comma-separated string.");
        }
    }
}
