<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('dateStart', null, [
                'widget' => 'single_text',
            ])
            ->add('duration')
            ->add('dateLimitRegistration', null, [
                'widget' => 'single_text',
            ])
            ->add('maxRegistration')
            ->add('eventInfo')
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'id',
            ])
            ->add('eventOrganizer', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('participate', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
