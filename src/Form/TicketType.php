<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TicketType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", TextType::class, [

            ])
            ->add("type", ChoiceType::class, [
                'choices' => [
                    'Zamówienia' => 'Zamówienia',
                    'Sprawy Techniczne' => 'Sprawy Techniczne',
                    'Inne' => 'Inne',
                ],
            ])
            ->add("description", TextareaType::class)
            ->add('submit', SubmitType::class);
    }
}