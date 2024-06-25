<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Location;
use App\EventSubscriber\LocationSubscriber;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\NotNull;

class ActivityType extends AbstractType
{

    private Security $security;
    private LocationSubscriber $locationSubscriber;
    private EntityManagerInterface $entityManager;
    private CityRepository $cityRepository;

    public function __construct(Security               $security,
                                LocationSubscriber     $locationSubscriber,
                                CityRepository         $cityRepository,
                                EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->locationSubscriber = $locationSubscriber;
        $this->cityRepository = $cityRepository;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'choice_label' => 'name',
                'class' => Campus::class,
                'data' => $this->security->getUser()->getCampus()
            ])
            ->add('name')
            ->add('startDate', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
            ])->add('dateLimitForRegistration', DateType::class, [
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('duration')
            ->add('maxRegistrationNumber')
            ->add('description', TextareaType::class, [
                'required' => false
            ]);

        //étant donné que je ne charge aucun lieu à l'affichage du form, je rajoute un évènement preSubmit
        // pour charger des lieux en fonction de la ville

        //Utilisation d'une class subscriber
        //$builder->addEventSubscriber($this->locationSubscriber);



        //utilisation d'un listener
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);

    }

    public function getCities()
    {
        return $this->cityRepository->createQueryBuilder('c')->addOrderBy('c.name', 'DESC');
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        /**
         * @var Activity $data ;
         *
         */
        $data = $event->getData();

        $city = null;
        $locations = [];

        if ($data->getId()) {
            //récupération de l'instance de la ville en fonction de son id
            $city = $this->entityManager->getRepository(City::class)->find($data->getLocation()->getCity());

            $locations = [];

            if ($city) {
                //récupération de lieux par rapport à la ville
                $locations = $this->entityManager->getRepository(Location::class)->findBy(['city' => $data->getLocation()->getCity()]);
            }
        }

        $form->add('city', EntityType::class, [
            'placeholder' => "Choisir ville",
            'class' => City::class,
            'mapped' => false,
            'choice_label' => 'name',
            'data' => $city,
            //'query_builder' => [$this, 'getCities'], callable
            //With QueryBuilder Object
            'query_builder' => function (CityRepository $cityRepository) {
                return $cityRepository->createQueryBuilder('c')->addOrderBy('c.name', 'ASC');
            },
            'constraints' => [
                new NotNull(
                    [], "La ville est obligatoire")
            ]
        ]);

        //rédéfinition de l'élément de mon formulaire, avec les données récupérées
        $form->add('location', EntityType::class, [
            'label' => 'Lieu',
            'placeholder' => 'Choisir lieu',
            'choices' => $locations,
            'choice_label' => 'name',
            'class' => Location::class
        ]);

    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        //récupération de l'instance de la ville en fonction de son id
        $city = $this->entityManager->getRepository(City::class)->find($data['city']);

        $locations = [];

        if ($city) {
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            'required' => false
        ]);
    }
}
