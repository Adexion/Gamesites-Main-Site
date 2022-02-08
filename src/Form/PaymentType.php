<?php

namespace App\Form;

use App\Entity\Price;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Hostname;
use Symfony\Component\Validator\Constraints\Regex;

class PaymentType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price', EntityType::class, [
                'label' => 'Okres',
                'class' => Price::class,
                'choice_label' => 'name'
            ])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('domain', TextType::class, [
                'label' => 'Domena',
                'help' => 'format: domena.pl',
                'constraints' => [
                    new Hostname()
                ]
            ])
            ->add('discountCode', TextType::class, [
                'label' => 'Kod rabatowy',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^GS([A-Z0-9]){14}$/',
                        'match' => true,
                        'message' => 'Kod rabatowy ma nieprawidłowy format'
                    ])
                ]
            ])
            ->add('payment', ChoiceType::class, [
                'attr' => ['class' => 'payment-container'],
                'row_attr' => ['class' => 'payment'],
                'choice_attr' => [
                    'HotPay' => ['class' => 'payment-choice payment-HotPay'],
                    'PaySafeCard' => ['class' => 'payment-choice payment-PaySafeCard']
                ],
                'choices' => ['HotPay' => 'HotPay', 'PaySafeCard' => 'PaySafeCard'],
                'expanded' => true,
                'label_attr' => ['class' => 'payment-label'],
                'label' => 'Forma Płatności'
            ])
            ->add('vat', CheckboxType::class, [
                'label' => 'Faktura VAT',
                'attr' => ['checked' => true]
            ])
            ->add('submit', SubmitType::class,[
                'attr' => ['class' => 'btn-primary btn-custom w-100 mt-0']
            ]);
    }
}