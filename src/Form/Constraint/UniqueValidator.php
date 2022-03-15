<?php

namespace App\Form\Constraint;

use Doctrine\Persistence\ManagerRegistry;
use http\Exception\RuntimeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueValidator extends ConstraintValidator
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Unique) {
            throw new UnexpectedTypeException($constraint, Unique::class);
        }
        if (!$constraint->class) {
            throw new RuntimeException("Field class is not correct set");
        }

        if (!empty($this->registry->getRepository($constraint->class)->findBy([$constraint->field => $value]))) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}