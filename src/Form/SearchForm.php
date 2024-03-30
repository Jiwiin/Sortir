<?php

namespace App\Form;

use App\Data\SearchData;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', TextType::class, [
                'label' => 'Le nom de la sortie contient',
                'required' => false,
                'attr' => [
                    'placeholder' => 'search'
                ]
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus :',
                'required' => false,
                'class' => Campus::class,
                'data' => $options['selectedCampus'],
                'multiple' => false,
                'placeholder' => false,
                ])
            ->add('startDate', DateType::class, [
                'label' => 'Entre',
                'html5' => true,
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Et',
                'required' => false,
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('organized', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
            ])
            ->add('registered', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false,
            ])
            ->add('notRegistered', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false,
            ])
            ->add('eventCompleted', CheckboxType::class, [
                'label' => 'Sorties terminées',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class' => SearchData::class,
           'method' => 'GET',
           'csrf_protection' => false,
            'selectedCampus' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

}