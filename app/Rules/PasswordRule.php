<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordRule implements ValidationRule
{

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[_)(*&^%$#@!]).{8,}$/";

        if (!preg_match($pattern, $value)) {
            $fail('رمز عبور باید شامل حداقل یک عدد، یک حرف بزرگ، یک حرف کوچک و یک کاراکتر خاص (_)(*&^%$#@!) باشد.');
        }
    }
}
