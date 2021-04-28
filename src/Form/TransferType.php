<?php

namespace App\Form;

use App\Entity\Transfer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Ajouter votre fichier',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/*',
                            'video/*',
                            'audio/*',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'application/vnd.ms-excel',
                            'application/zip',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Ce type de fichier n\'est pas autorisé',
                        'maxSizeMessage' => 'La taille de votre fichier ne doit pas dépasser 10Mo',
                    ]),
                ],
            ])
            ->add('recipient', EmailType::class, [
                'label' => 'Envoyer à',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transfer::class,
        ]);
    }
}
