<?php

namespace App\Form;

use App\Entity\Campus;
use App\Form\Model\SearchActivity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Le nom de la sortie contient : ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher...'
                ]
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'label' => 'Campus',
                'choice_label' => 'name',
                'placeholder' => 'Choisir campus...',
                'required' => false
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Entre ',
                'required' => false
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'et ',
                'required' => false
            ])
            ->add('organiser', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur/trice",
                'required' => false
            ])
            ->add('isParticipant', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit/e",
                'required' => false
            ])
            ->add('isNotParticipant', CheckboxType::class, [
                'label' => "Sorties auxquelles je ne suis pas inscrit/e",
                'required' => false
            ])
            ->add('oldActivity', CheckboxType::class, [
                'label' => "Sorties passées",
                'required' => false
            ]);
    }

    //à redéfinir pour imposer un nom pour le form
    public function getBlockPrefix(): string
    {
        return "searchActivity";
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchActivity::class,
            'csrf_protection' => false,
            'attr' => ['id' => 'searchActivity']
        ]);
    }
}
