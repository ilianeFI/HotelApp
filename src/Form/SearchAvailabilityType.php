<?php

namespace App\Form;

use App\Dto\SearchAvailability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchAvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Check-in',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Check-out',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('personnes', IntegerType::class, [
                'label' => 'Guests',
                'attr' => ['class' => 'form-control', 'min' => 1],
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Vérifier la disponibilité',
                'attr' => ['class' => 'btn btn-gold w-100 mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchAvailability::class,
            'method' => 'POST',
        ]);
    }
}
