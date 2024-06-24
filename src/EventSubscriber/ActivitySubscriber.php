<?php

namespace App\EventSubscriber;

use App\Entity\Activity;
use App\Entity\State;
use App\Repository\StateRepository;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class ActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(private StateRepository $stateRepository)
    {
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof Activity) {
            $this->updateActivity($object);
        }
    }

    private function updateActivity(Activity $activity)
    {
        //update de la sortie uniquement si cloturée ou ouverte
        if ($activity->getState()->getStateCode() == State::CLOSED || $activity->getState()->getStateCode() == State::OPEN) {

            $closedState = $this->stateRepository->findOneBy(["stateCode" => State::CLOSED]);
            $openState = $this->stateRepository->findOneBy(["stateCode" => State::OPEN]);

            //si il reste de la place et que la date limite d'inscription n'est pas dépassé
            //alors je passe l'activité en ouverte
            if ($activity->getMaxRegistrationNumber() > $activity->getParticipants()->count() && new \DateTime() < $activity->getDateLimitForRegistration()) {
                $activity->setState($openState);
            } else {
                //sinon je la ferme
                $activity->setState($closedState);
            }

        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate => 'preUpdate',
        ];
    }
}
