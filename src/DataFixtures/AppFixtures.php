<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private ObjectManager $manager;
    private Generator $generator;
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->generator = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->addStates();
        $this->addCities();
        $this->addLocations();
        $this->addCampus();
        $this->addUsers();
        $this->addActivities();

    }

    private function addStates()
    {
        $states = [
            ['code' => 'CRE', 'label' => 'En création'],
            ['code' => 'OPN', 'label' => 'Ouverte'],
            ['code' => 'CLO', 'label' => 'Clôturée'],
            ['code' => 'AIP', 'label' => 'Activité en cours'],
            ['code' => 'AFI', 'label' => 'Activité terminée'],
            ['code' => 'HIS', 'label' => 'Activité historisée'],
            ['code' => 'CAN', 'label' => 'Annulée']
        ];

        foreach ($states as $s) {

            $state = new State();
            $state->setStateCode($s['code'])->setLabel($s['label']);
            $this->manager->persist($state);
        }

        $this->manager->flush();

    }

    private function addCities(int $number = 20)
    {

        $city = new City();
        $city->setName("Rennes")->setZipcode(35000);

        $this->manager->persist($city);

        for ($i = 0; $i <= $number; $i++) {

            $city = new City();
            $city->setName($this->generator->city)->setZipcode(str_replace(' ', '', $this->generator->postcode));

            $this->manager->persist($city);
        }

        $this->manager->flush();

    }

    private function addLocations(int $number = 50)
    {
        $cities = $this->manager->getRepository(City::class)->findAll();

        $locationNames = ['Golf', 'Piscine', 'Bar', 'Bar',
            'Cinéma', 'Librairie', 'Retaurant', 'Tennis', 'Ping-Pong',
            "Course d'orientation", "Festival", "Concert", "Curling"];

        for ($i = 0; $i <= $number; $i++) {

            /**
             * @var City $city
             */
            $city = $this->generator->randomElement($cities);

            $location = new Location();
            $location
                ->setName($this->generator->randomElement($locationNames) . ' / ' . $city->getName())
                ->setCity($city)
                ->setLatitude($this->generator->latitude)
                ->setLongitude($this->generator->longitude)
                ->setStreet($this->generator->streetAddress);

            $this->manager->persist($location);
        }

        $this->manager->flush();

    }

    private function addCampus()
    {

        $names = ['Nantes', 'Niort', "Quimper", "Rennes"];

        foreach ($names as $name) {

            $campus = new Campus();
            $campus->setName($name);

            $this->manager->persist($campus);
        }

        $this->manager->flush();
    }

    private function addUsers(int $number = 150)
    {

        $campus = $this->manager->getRepository(Campus::class)->findAll();

        //User Test
        $user = new User();
        $user
            ->setEmail("sly@mail.com")
            ->setRoles(['ROLE_USER'])
            ->setCampus($this->generator->randomElement($campus))
            ->setFirstname("Sylvain")
            ->setLastname("Tropée")
            ->setPassword($this->encoder->hashPassword($user, '123'))
            ->setPhone($this->generator->phoneNumber);

        $this->manager->persist($user);


        for ($i = 0; $i <= $number; $i++) {
            $user = new User();
            $user
                ->setEmail($this->generator->email)
                ->setRoles(['ROLE_USER'])
                ->setCampus($this->generator->randomElement($campus))
                ->setFirstname($this->generator->firstName)
                ->setLastname($this->generator->lastName)
                ->setPassword($this->encoder->hashPassword($user, '123'))
                ->setPhone($this->generator->phoneNumber);

            $this->manager->persist($user);
        }

        $this->manager->flush();

    }

    private function addActivities(int $number = 150)
    {

        $state = $this->manager->getRepository(State::class)->findOneBy(['stateCode' => 'OPN']);
        $users = $this->manager->getRepository(User::class)->findAll();
        $locations = $this->manager->getRepository(Location::class)->findAll();

        for ($i = 0; $i <= $number; $i++) {

            $organiser = $this->generator->randomElement($users);

            $activity = new Activity();
            $activity
                ->setCampus($organiser->getCampus())
                ->setOrganiser($organiser)
                ->setName($this->generator->words(1, true))
                ->setLocation($this->generator->randomElement($locations))
                ->setDescription($this->generator->words(30, true))
                ->setDuration($this->generator->numberBetween(60, 320))
                ->setMaxRegistrationNumber($this->generator->numberBetween(10, 100))
                ->setState($state)
                ->setStartDate($this->generator->dateTimeBetween("-45 day", "+1 month"));
            $maxDate = clone $activity->getStartDate();
            $activity->setDateLimitForRegistration($this->generator->dateTimeBetween($maxDate->modify('-5 day'), $maxDate))
                ->setParticipants(new ArrayCollection($this->generator->randomElements($users, $activity->getMaxRegistrationNumber() - 1)));

            $this->manager->persist($activity);
        }

        $this->manager->flush();

    }
}
