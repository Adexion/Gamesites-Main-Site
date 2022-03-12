<?php

namespace App\Form\Constraint;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DomainValidator extends ConstraintValidator
{
    private string $ip;

    public function __construct(ParameterBagInterface $params)
    {
        $this->ip = $params->get('ip');
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Domain) {
            throw new UnexpectedTypeException($constraint, Domain::class);
        }

        if (gethostbyname($value) !== $this->ip) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ ip }}', $this->ip)
                ->addViolation();
        }
    }
}