<?php

namespace App\Form;

use App\Entity\Application;
use App\Form\Constraint\Unique;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApplicationSetupType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Unique(
                        [
                            'message' => 'Aplikacja o tej nazwie został juz utworzony. Jeżeli jesteś jego właścicielem skontaktuj się z supportem.',
                            'class' => Application::class,
                            'field' => 'name',
                        ]
                    ),
                    new Length(['min' => 6, 'max' => 255]),
                ],
            ])
            ->add('configure', SubmitType::class);
    }
}