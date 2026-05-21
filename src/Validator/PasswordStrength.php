<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class PasswordStrength extends Constraint
{
    public string $message = 'Password must be at least 8 characters and include uppercase, lowercase, and a number.';
}
