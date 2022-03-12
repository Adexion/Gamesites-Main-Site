<?php

namespace App\Form\Constraint;

use App\Repository\OrderRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrderValidator extends ConstraintValidator
{
    private OrderRepository $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Order) {
            throw new UnexpectedTypeException($constraint, Order::class);
        }

        $order = $this->repository->findOneBy(['coupon' => $value]);
        if (!$order || $order->getExpiryDate()->format('YmdHis') < date('YmdHis')) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}