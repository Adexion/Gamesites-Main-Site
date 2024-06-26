<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserAddressType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Full name'
            ])
            ->add('nickname', TextType::class)
            ->add('street', TextType::class)
            ->add('houseNumber', TextType::class)
            ->add('apartmentNumber', TextType::class, ['required' => false])
            ->add('city', TextType::class)
            ->add('postCode', TextType::class)
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Address::class);
    }
}