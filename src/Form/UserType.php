<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
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
            ])
            // Champ roles avec type ChoiceType pour sélectionner des rôles
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                ],
                'multiple' => true, // Permettre plusieurs sélections
                'expanded' => true, // Afficher des cases à cocher
            ])
            // Champ password avec type PasswordType et contraintes
            ->add('password', PasswordType::class, [
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
            ])
            // Champ isVerified avec type CheckboxType
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
            ])
            // Champ lastname avec type TextType et contraintes
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom de famille ne doit pas être vide.',
                    ]),
                ],
            ])
            // Champ firstname avec type TextType et contraintes
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom ne doit pas être vide.',
                    ]),
                ],
            ])
            // Champ birthAt avec type DateTimeType pour la date de naissance
            ->add('birthAt', null, [
                'widget' => 'single_text',
            ])
            // Champ createdAt avec type DateTimeType pour la date de création
            ->add('createdAt', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de création ne doit pas être vide.',
                    ]),
                    new Assert\DateTime([
                        'message' => 'Veuillez entrer une date valide.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
