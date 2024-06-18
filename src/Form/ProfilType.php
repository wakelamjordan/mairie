<?php

namespace App\Form;

use App\Entity\User;
use Webmozart\Assert\Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilType extends AbstractType
{
    public function __construct(
        private Security $security,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // $buttonDesabled = $options['buttonDesabled'];
        $builder
            ->add('email', EmailType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => $this->security->getUser()->getUserIdentifier(),
                    'autocomplete' => 'off',
                ],

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
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prénom ne doit pas être vide.',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le prénom ne peut pas contenir plus de {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prénom ne doit pas être vide.',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le prénom ne peut pas contenir plus de {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add(
                'birthAt',
                null,
                [
                    'widget' => 'single_text',
                    'constraints' => [
                        // new LowerThanOrEqual([
                        //     'value' => (new \DateTime('now'))->modify('-10 years'),
                        //     'message' => 'La date d\'anniversaire doit être supérieure ou égale à {{ compared_value }}',
                        // ]),
                        new LessThanOrEqual([
                            'value' => (new \DateTime('now'))->modify('-10 years'),
                            'message' => 'Votre date de naissance est invalide',
                        ]),
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                'autocomplete' => 'off',
            ]
        ]);
    }

    // public function validateBirthdate($value, ExecutionContextInterface $context)
    // {
    //     // Vérifie si la date est dans le futur
    //     if ($value > new \DateTime()) {
    //         $context->buildViolation('La date de naissance ne peut pas être dans le futur.')
    //             ->addViolation();
    //     }

    //     // Vérifie si la date est antérieure à 1900
    //     if ($value < new \DateTime('1900-01-01')) {
    //         $context->buildViolation('La date de naissance doit être postérieure à 1900.')
    //             ->addViolation();
    //     }
    // }
}
