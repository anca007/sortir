<?php

namespace App\Utils;

use App\Entity\Activity;
use App\Entity\State;
use App\Repository\ActivityRepository;
use App\Repository\StateRepository;
use Doctrine\ORM\EntityManagerInterface;

class ActivityState
{

    public function __construct(
        private ActivityRepository $activityRepository,
        private StateRepository $stateRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function update(){

        $activities = $this->activityRepository->findActivitiesToUpdate();
        $states = $this->stateRepository->findAll();
        $now = new \DateTime();
        
        $histoState = "";
        $endState = "";
        $ipState = "";
        $cloState = "";

        foreach ($states as $state) {
           switch ($state->getStateCode()){
               case State::PASSED :
                   $histoState = $state;
                   break;
               case State::IS_FINISHED :
                   $endState = $state;
                   break;
               case State::IN_PROGRESS:
                   $ipState = $state;
                   break;
               case State::CLOSED :
                   $cloState = $state;
                   break;
           }
        }

        /**
         * @var Activity $activity
         */
        foreach ($activities as $activity) {

            $endOfActitivyDate = clone $activity->getStartDate();
            $endOfActitivyDate->modify("+ " . $activity->getDuration() . " minute");

            //test si historisé
            if($now > $endOfActitivyDate && $endOfActitivyDate->diff($now)->m >= 1){
                $activity->setState($histoState);
            //test si fini
            }elseif ($now > $endOfActitivyDate){
                $activity->setState($endState);
            //test si en cours
            }elseif ($now > $activity->getStartDate()){
                $activity->setState($ipState);
            //test si date de cloture dépassé
            }elseif($now > $activity->getDateLimitForRegistration()){
                $activity->setState($cloState);
            }

            $this->entityManager->persist($activity);
        }

        $this->entityManager->flush();

    }



}