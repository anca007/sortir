<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('phone')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation'],
                'mapped' => false,
                'required' => false
            ])
            ->add('photo', FileType::class, [
                'label' => "Photo de profil",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg'
                        ],
                        'maxSize' => '7m',
                        'mimeTypesMessage' => "C'est pas une image",
                        'maxSizeMessage' => "Trop grosse l'image, trop grosse !"
                    ])
                ]

            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
