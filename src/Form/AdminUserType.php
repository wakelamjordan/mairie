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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints as Assert;


class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => $options['data']->getEmail(),
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'adresse email ne doit pas être vide.',
                    ]),
                    new Assert\Email([
                        'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
                    ]),
                ],

            ])
            ->add('roles', CheckboxType::class, [
                'label' => 'Admin',
                'required' => false,
                'mapped' => false, // Ne pas mapper directement à l'entité User
                'data' => in_array('ROLE_ADMIN', $options['data']->getRoles()), // Vérifie si l'utilisateur a le rôle admin
                'label_attr' => [
                    'class' => 'badge text-bg-warning fs-5'
                ],
                // 'attr'=>[
                //     'class'=>'pt-3'
                // ]
            ])
            // ->add('password', RepeatedType::class, [
            //     'mapped' => false,
            //     'type' => PasswordType::class,
            //     'required' => false, // Le champ n'est pas requis pour soumettre le formulaire
            //     'attr' => [
            //         'autocomplete' => 'new-password',
            //     ],
            //     'first_options' => [
            //         'label' => 'Mot de passe',
            //         'constraints' => [
            //             // Utilisation de la contrainte personnalisée qui ne s'appliquera que si le champ est rempli
            //             new Length([
            //                 'min' => 8,
            //                 'max' => 25,
            //                 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
            //                 'maxMessage' => 'Le mot de passe ne peut pas contenir plus de {{ limit }} caractères.',
            //             ]),
            //         ],
            //     ],
            //     'second_options' => [
            //         'label' => 'Confirmer le mot de passe',
            //         'attr' => [
            //             'autocomplete' => 'new-password',
            //         ],
            //     ],
            //     'invalid_message' => 'Les mots de passe ne sont pas identiques.',
            // ])
            // ->add('isVerified')
            ->add('lastname')
            ->add('firstname')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
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
