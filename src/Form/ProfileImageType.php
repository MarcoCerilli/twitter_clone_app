<?php
// src/Form/ProfileImageType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;

class ProfileImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('profileImage', FileType::class, [
                'label' => 'Immagine del Profilo (file JPG, PNG)',
                'mapped' => false, // FONDAMENTALE: dice a Symfony di non associare questo campo a una proprietà dell'entità
                'required' => true,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Per favore carica un\'immagine valida (JPG o PNG).',
                    ])
                ],
            ])
            ->add('Save', SubmitType::class, [
                'label' => 'Salva Immagine'
            ]);
    }
}
