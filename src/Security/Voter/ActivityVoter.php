<?php

namespace App\Security\Voter;

use App\Entity\Activity;
use App\Entity\State;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ActivityVoter extends Voter
{
    public const QUIT = 'ACTIVITY_QUIT';
    public const REGISTER = 'ACTIVITY_REGISTER';
    public const CANCEL = 'ACTIVITY_CANCEL';
    public const EDIT = 'ACTIVITY_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::QUIT, self::REGISTER, self::EDIT, self::CANCEL])
            && $subject instanceof Activity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        /**
         * @var Activity $subject
         */
        switch ($attribute) {
            case self::QUIT:
                if (new \DateTime() < $subject->getDateLimitForRegistration()
                    && $subject->getParticipants()->contains($user)
                    && ($subject->getState()->getStateCode() == State::CLOSED || $subject->getState()->getStateCode() == State::OPEN)) {
                    return true;
                }
                break;
            case self::REGISTER:
                if ($subject->getParticipants()->count() < $subject->getMaxRegistrationNumber()
                    && new \DateTime() < $subject->getDateLimitForRegistration()
                    && !$subject->getParticipants()->contains($user)
                    && $subject->getState()->getStateCode() == State::OPEN
                ) {
                    return true;
                }
                break;
            case self::CANCEL:
                if($subject->getOrganiser() === $user && ($subject->getState()->getStateCode() == State::OPEN || $subject->getState()->getStateCode() == State::CLOSED )){
                    return true;
                }
                break;
            case self::EDIT:
                if($subject->getOrganiser() === $user && $subject->getState()->getStateCode() == State::CREATION){
                    return true;
                }
                break;
        }

        return false;
    }
}
