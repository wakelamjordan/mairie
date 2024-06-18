<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
                    new Assert\Email([
                        'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
                    ]),
                ],

            ])
            ->add('roles', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                ],
                'label_attr' => [
                    'class' => 'badge text-bg-warning fs-5'
                ],
                'expanded' => true,  // Pour afficher les choix sous forme de boutons radio ou cases à cocher
                'multiple' => true,  // Pour permettre à l'utilisateur de sélectionner plusieurs rôles
                'required' => false, // Si vous ne voulez pas rendre ce champ obligatoire
            ])
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom ne doit pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le prénom ne peut pas contenir plus de {{ limit }} caractères.',
                    ]),
                ],
            ])
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
            ])
            ->add('birthAt', null, [
                'widget' => 'single_text',
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => (new \DateTime('now'))->modify('-10 years'),
                        'message' => 'Votre date de naissance est invalide',
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
