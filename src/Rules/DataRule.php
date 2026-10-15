<?php

namespace Laracroft\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

abstract class DataRule implements ValidationRule
{
    public const array RULES = [];

    public function __construct(private readonly mixed $ruleKey)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail) : void
    {
        $rules = is_string($this->ruleKey) || is_int($this->ruleKey)
            ? static::RULES[$this->ruleKey] ?? []
            : [];

        if ( !is_array($rules) || !in_array($value, $rules, true) ) {
            $fail("The {$attribute} is invalid for the given type.");
        }
    }
}
