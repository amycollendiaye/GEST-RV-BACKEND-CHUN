<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Telephone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
  public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^(\+221|00221)?[7-9][0-9]{8}$/', $value)) {
            $fail("Le $attribute n'est pas un numéro de téléphone valide.");
        }
    }
}
