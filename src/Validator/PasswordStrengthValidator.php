<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PasswordStrengthValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordStrength) {
            throw new UnexpectedTypeException($constraint, PasswordStrength::class);
        }

        if (!\is_string($value) || $value === '') {
            return;
        }

        if (\strlen($value) < 8
            || !preg_match('/[A-Z]/', $value)
            || !preg_match('/[a-z]/', $value)
            || !preg_match('/\d/', $value)
        ) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
