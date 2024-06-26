<?php


namespace App\Form\Model;


use App\Entity\Campus;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class SearchActivity
{

    private ?string $name = "";
    private ?Campus $campus = null;

    #[Assert\LessThan(propertyPath: 'endDate', message: 'La date de début doit être inférieur à la date de fin !')]
    private ?\DateTimeInterface $startDate = null;


    #[Assert\GreaterThan(propertyPath: 'startDate', message: 'La date de fin doit être supérieur à la date de début !')]
    private ?\DateTimeInterface $endDate = null;
    private ?bool $organiser = null;
    private ?bool $isParticipant = null;
    private ?bool $isNotParticipant = null;
    private ?bool $oldActivity = null;

    /**
     * @return mixed
     */
    public function getIsParticipant()
    {
        return $this->isParticipant;
    }

    /**
     * @param mixed $isParticipant
     */
    public function setIsParticipant($isParticipant): void
    {
        $this->isParticipant = $isParticipant;
    }

    /**
     * @return mixed
     */
    public function getIsNotParticipant()
    {
        return $this->isNotParticipant;
    }

    /**
     * @param mixed $isNotParticipant
     */
    public function setIsNotParticipant($isNotParticipant): void
    {
        $this->isNotParticipant = $isNotParticipant;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCampus()
    {
        return $this->campus;
    }

    /**
     * @param mixed $campus
     */
    public function setCampus($campus): void
    {
        $this->campus = $campus;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getOrganiser()
    {
        return $this->organiser;
    }

    /**
     * @param mixed $organiser
     */
    public function setOrganiser($organiser): void
    {
        $this->organiser = $organiser;
    }

    /**
     * @return mixed
     */
    public function getOldActivity()
    {
        return $this->oldActivity;
    }

    /**
     * @param mixed $oldActivity
     */
    public function setOldActivity($oldActivity): void
    {
        $this->oldActivity = $oldActivity;
    }




}