<?php

namespace App\Form\Constraint;

use App\Repository\OrderRepository;
use App\Repository\ApplicationRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrderValidator extends ConstraintValidator
{
    private OrderRepository $orderRepository;
    private ApplicationRepository $applicationRepository;

    public function __construct(OrderRepository $orderRepository, ApplicationRepository $applicationRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->applicationRepository = $applicationRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Order) {
            throw new UnexpectedTypeException($constraint, Order::class);
        }

        $order = $this->applicationRepository->findOneBy(['coupon' => $value]) ?: $this->orderRepository->findOneBy(['coupon' => $value, 'isActive' => true]);
        if (!$order || $order->getExpiryDate()->format('YmdHis') < date('YmdHis')) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}