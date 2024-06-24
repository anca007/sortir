<?php

namespace App\EventSubscriber;

use App\Entity\City;
use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class LocationSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        //récupération de l'instance de la ville en fonction de son id
        $city = $this->entityManager->getRepository(City::class)->find($data['city']);

        $locations = [];

        if($city){
            //récupération de lieux par rapport à la ville
            $locations = $this->entityManager->getRepository(Location::class)->findBy(['city' => $data['city']]);
        }

        //rédéfinition de l'élément de mon formulaire, avec les données récupérées
        $form->add('location', EntityType::class, [
            'label' => 'Lieu',
            'placeholder' => 'Choisir lieu',
            'choices' => $locations,
            'choice_label' => 'name',
            'class' => Location::class
        ]);

    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }
}
