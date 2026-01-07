<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'Numéro de chambre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 101'],
                'label_attr' => ['class' => 'form-label mt-3']
            ])
            ->add('price', NumberType::class, [
                'attr' => ['class'=>'form-control', 'min'=>0, 'step'=>1, 'placeholder'=>'Ex: 150'],
                ])
            ->add('capacity', NumberType::class, [
                'attr' => ['class'=>'form-control', 'min'=>1, 'step'=>1, 'placeholder'=>'Ex: 2'],
                ])
            ->add('isAvailable', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
                'attr' => ['class' => 'form-check-input mt-2'],
                'label_attr' => ['class' => 'form-check-label']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Description de la chambre', 'rows' => 4],
                'label_attr' => ['class' => 'form-label mt-3']
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (JPG ou PNG)',
                'mapped' => false, 
                'required' => false,
                'attr' => ['class' => 'form-control mt-2'],
                'label_attr' => ['class' => 'form-label mt-3'],
                'constraints' => [
                    new File(
                        maxSize: '20M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                        ],
                        mimeTypesMessage: 'Veuillez télécharger une image valide (JPG ou PNG).'
                    )
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
