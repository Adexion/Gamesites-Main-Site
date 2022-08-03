<?php

namespace App\Form;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class NotificationType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['constraints' => [new Length(['max' => 255])]])
            ->add('text', TextareaType::class)
            ->add('isEmail', CheckboxType::class, ['required' => false])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.removed = false')
                        ->orderBy('u.isActive', 'DESC')
                        ->orderBy('u.roles', 'DESC')
                        ->orderBy('u.email', 'ASC');
                },
                'choice_label' => 'userIdentifier',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'size' => '5'
                ],
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('data_class', Notification::class);
    }
}