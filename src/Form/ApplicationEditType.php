<?php

namespace App\Form;

use App\Entity\Application;
use App\Entity\Workspace;
use App\Form\Constraint\Unique;
use App\Repository\WorkspaceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ApplicationEditType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'constraints' => [
                    new Length(['min' => 8, 'max' => 255])
                ]
            ])
            ->add('workspace', EntityType::class, [
                'placeholder' => '--- Brak ---',
                'required' => false,
                'class' => Workspace::class,
                'choice_label' => 'name',
                'query_builder' => function (WorkspaceRepository $repository) use ($options){
                    return $repository->createQueryBuilder('w')
                        ->join('w.users', 'u')
                        ->where('u.id = :uid')
                        ->setParameter(':uid', $options['user']);
                }
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('user', null)
            ->setDefault('name', '');
    }
}