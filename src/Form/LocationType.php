<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Location;
use App\Repository\CityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('zipCode', null, [
                'mapped' => false
            ])
            ->add('street')
            ->add('latitude')
            ->add('longitude')

            ->add('city', EntityType::class, [
                'choice_label' => 'name',
                'class' => City::class,
                'query_builder' => function(CityRepository $cityRepository){
                    return $cityRepository->createQueryBuilder('c')->addOrderBy('c.name', 'ASC');
                }
            ])
            ->add('name')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
            'required' => false
        ]);
    }
}
