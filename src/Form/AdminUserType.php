<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\ConfirmationEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', CheckboxType::class, [
                'label' => 'Rôle administrateur',
                'required' => false,
                'mapped' => false, // Ne pas mapper directement à l'entité User
                'data' => in_array('ROLE_ADMIN', $options['data']->getRoles()), // Vérifie si l'utilisateur a le rôle admin
            ])
            ->add('password', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'required' => false, // Le champ n'est pas requis pour soumettre le formulaire
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
                'first_options' => [
                    'label' => 'Mot de passe',
                    'constraints' => [
                        // Utilisation de la contrainte personnalisée qui ne s'appliquera que si le champ est rempli
                        new Length([
                            'min' => 8,
                            'max' => 25,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                            'maxMessage' => 'Le mot de passe ne peut pas contenir plus de {{ limit }} caractères.',
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne sont pas identiques.',
            ])
            ->add('isVerified')
            ->add('lastname')
            ->add('firstname')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('birthAt', null, [
                'widget' => 'single_text',
            ])
            ->add('confirmationEmail', EntityType::class, [
                'class' => ConfirmationEmail::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
