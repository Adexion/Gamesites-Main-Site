<?php

namespace App\Form;

use App\Entity\Application;
use App\Entity\Workspace;
use App\Form\Constraint\Unique;
use App\Repository\WorkspaceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ApplicationEditType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Unique(
                        [
                            'message' => 'Aplikacja o tej nazwie został juz utworzony. Jeżeli jesteś jego właścicielem skontaktuj się z supportem.',
                            'class' => Application::class,
                            'field' => 'name',
                            'skip' => $options['name']
                        ]
                    ),
                    new Length(['min' => 6, 'max' => 255]),
                ],
            ])
            ->add('workspace', EntityType::class, [
                'placeholder' => '--- Brak ---',
                'required' => false,
                'class' => Workspace::class,
                'choice_label' => 'name',
                'query_builder' => function (WorkspaceRepository $repository) use ( $options){
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
            ->setDefault('name', '')
            ->setDefault('data_class', Application::class);
    }
}