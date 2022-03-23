<?php

namespace App\Form\Constraint;

use App\Entity\Application;
use App\Entity\Order as OrderEntity;
use App\Repository\ApplicationRepository;
use App\Repository\OrderRepository;
use DateTime;
use Exception;
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

    /**
     * @throws Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Order) {
            throw new UnexpectedTypeException($constraint, Order::class);
        }

        $order = $this->applicationRepository->findOneBy(['coupon' => $value]) ?: $this->orderRepository->findOneBy(
            ['coupon' => $value, 'isActive' => true]
        );

        if (empty($order)) {
            return $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

        $date = $order instanceof OrderEntity ? new DateTime($order->getExpiryDate()) : $order->getExpiryDate();

        if ($date->format('YmdHis') < date('YmdHis')) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

        if ($order instanceof Application) {
            $this->context->buildViolation($constraint->messageUsed)->addViolation();
        }
    }
}