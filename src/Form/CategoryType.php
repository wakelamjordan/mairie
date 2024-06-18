<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label')
            ->add('route')
            ->add('roles', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                ],
                'label_attr' => [
                    'class' => 'badge text-bg-warning fs-5'
                ],
                'expanded' => true,  // Pour afficher les choix sous forme de boutons radio ou cases à cocher
                'multiple' => true,  // Pour permettre à l'utilisateur de sélectionner plusieurs rôles
                'required' => false, // Si vous ne voulez pas rendre ce champ obligatoire
            ])
            ->add('rank')
            ->add('parent', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
