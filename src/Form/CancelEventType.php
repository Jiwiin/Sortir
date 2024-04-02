<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class CancelEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('eventInfo', TextareaType::class, [
                'label' => 'Motif',
                'data' => 'Sortie annulée',
                'required' => true,
    ]);
    }
}