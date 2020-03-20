<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IntegerValueValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IntegerValue) {
            throw new UnexpectedTypeException($constraint, IntegerValue::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (is_int($value)) {
            return;
        }

        if (is_string($value) && ctype_digit($value)) {
            return;
        }

        if (false !== filter_var($value, FILTER_VALIDATE_INT)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setInvalidValue($value)
            ->setParameter('{{ value }}', $value)
            ->setCode(IntegerValue::INTEGER_VALUE)
            ->addViolation()
        ;
    }
}
