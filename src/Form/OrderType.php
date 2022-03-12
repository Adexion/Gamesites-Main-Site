<?php

namespace App\Form;

use App\Form\Constraint\Domain;
use App\Form\Constraint\Order;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('domain', TextType::class, [
                'attr' => ['placeholder' => 'np. gamesites.pl'],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6, 'max' => 255]),
                    new Domain(),
                ],
            ])
            ->add('coupon', TextType::class, [
                'attr' => ['placeholder' => 'format: GSXXXXXXXXXX'],
                'constraints' => [
                    new NotBlank(),
                    new Length(12),
                    new Order()
                ],
            ])
            ->add('createOrder', SubmitType::class);
    }
}