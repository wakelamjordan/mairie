<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ email avec type EmailType et contraintes
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'adresse email ne doit pas être vide.',
                    ]),
                    new Assert\Email([
                        'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
                    ]),
                ],

            ]);
        // // Champ password avec type PasswordType et contraintes
        // ->add('password', PasswordType::class, [
        //     'constraints' => [
        //         new Assert\NotBlank([
        //             'message' => 'Le mot de passe ne doit pas être vide.',
        //         ]),
        //         new Assert\Length([
        //             'min' => 8,
        //             'max' => 25,
        //             'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        //             'maxMessage' => 'Le mot de passe ne peut pas contenir plus de {{ limit }} caractères.',
        //         ]),
        //     ],
        // ])
        // // Champ terms avec type CheckboxType et contraintes
        // ->add('terms', CheckboxType::class, [
        //     'mapped' => false,
        //     'constraints' => [
        //         new Assert\IsTrue([
        //             'message' => 'Vous devez accepter les termes et conditions.',
        //         ]),
        //     ],
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
