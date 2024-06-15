<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\DateTime;
use Webmozart\Assert\Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
            ->add('lastname')
            ->add('firstname')
            ->add('birthAt', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de naissance ne doit pas être vide.',
                    ]),
                    new DateTime([
                        'message' => 'Veuillez entrer une date de naissance valide.',
                    ]),
                    new Callback([$this, 'validateBirthdate']),
                ],
            ]);
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

    public function validateBirthdate($value, ExecutionContextInterface $context)
    {
        // Vérifie si la date est dans le futur
        if ($value > new \DateTime()) {
            $context->buildViolation('La date de naissance ne peut pas être dans le futur.')
                ->addViolation();
        }

        // Vérifie si la date est antérieure à 1900
        if ($value < new \DateTime('1900-01-01')) {
            $context->buildViolation('La date de naissance doit être postérieure à 1900.')
                ->addViolation();
        }
    }
}
