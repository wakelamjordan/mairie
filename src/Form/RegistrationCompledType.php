<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationCompledType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ email avec type EmailType et contraintes
            // ->add('email', EmailType::class, [
            //     'constraints' => [
            //         new Assert\NotBlank([
            //             'message' => 'L\'adresse email ne doit pas être vide.',
            //         ]),
            //         new Assert\Email([
            //             'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
            //         ]),
            //     ],
            //     'attr' => [
            //         'readOnly' => true,
            //     ]
            // ])
            // Champ password avec type PasswordType et contraintes
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'Le mot de passe ne doit pas être vide.',
                        ]),
                        new Assert\Length([
                            'min' => 8,
                            'max' => 25,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                            'maxMessage' => 'Le mot de passe ne peut pas contenir plus de {{ limit }} caractères.',
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                ],
                'invalid_message' => 'Les mots de passe ne sont pas identiques.',
            ])
            // Champ lastname avec type TextType et contraintes
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom de famille ne doit pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le nom de famille ne peut pas contenir plus de {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'value' => null,
                ]
            ])
            // Champ firstname avec type TextType et contraintes
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom ne doit pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le prénom ne peut pas contenir plus de {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'value' => null,
                ]
            ])
            // Champ birthAt avec type DateType et contraintes

            // autres champs...
            ->add('birthAt', null, [
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
