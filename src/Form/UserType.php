<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe :',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('passwordConfirm', PasswordType::class, [
                'label' => 'Confirmer le mot de passe',
                'mapped' => false
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('username')
            ->add('firstname')
            ->add('lastname')
            ->add('phone')
            ->add('status')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
            ])
            ->add('participationEvents', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
