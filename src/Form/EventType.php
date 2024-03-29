<?php

namespace App\Form;


use App\Entity\City;
use App\Entity\Event;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
                'required' => true,
            ])
            ->add('dateStart', null, [
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie',
                'required' => true,
            ])
            ->add('dateLimitRegistration', null, [
                'widget' => 'single_text',
                'label' => 'Date limite d\'inscription',
                'required' => true,
            ])
            ->add('maxRegistration', null, [
                'label' => 'Nombre de places',
                'required' => true,
            ])
            ->add('duration', null, [
                'label' => 'Durée (en minutes)',
                'data' => 60,
                'required' => true,
            ])
            ->add('location', EntityType::class, [
                'label' => 'Lieu',
                'class' => Location::class,
                'choice_label' => 'name',
                'attr' => [
                    'id' => 'form_location'
                ]
            ])
            ->add('eventInfo', TextareaType::class, [
                'label' => 'Description et infos',
                'required' => false,
            ])
            ->add('state', SubmitType::class, [
                'label'=> 'Publier'
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
