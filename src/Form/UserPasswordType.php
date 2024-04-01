<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type'=> PasswordType::class,
                'first_options'=>[
                    'attr'=> [
                        'class' => 'form-control'
                    ],
                    'label'=>'Mot de passe actuel',
                ],
                'second_options'=>[
                    'attr'=> [
                        'class' => 'form-control'
                    ],
                    'label'=>'Confirmation de mot de passe actuel',
                ],
                'invalid_message'=>'Les mots de passe ne correspondent pas'
            ])
            ->add('newPassword', PasswordType::class, [
                'attr'=>['class'=> 'form-control'],
                'label'=>'Nouveau mot de passe',
                'constraints'=> [new Assert\NotBlank()]
            ]);
    }
}