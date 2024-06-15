<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\ConfirmationEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserType extends AbstractType
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
            ->add('password')
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
