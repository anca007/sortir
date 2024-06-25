<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Veuillez remplir le nom de la sortie')]
    private ?string $name;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $startDate;

    #[ORM\Column(type: 'integer')]
    private ?int $duration;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateLimitForRegistration;

    #[ORM\Column(type: 'integer')]
    private ?int $maxRegistrationNumber;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'organiserActivities')]
    private ?User $organiser;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'participatingActivities')]
    private Collection $participants;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: State::class, inversedBy: 'activities')]
    private ?State $state;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[Assert\NotNull(message: 'Le lieu est obligatoire')]
    private ?Location $location;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Campus::class)]
    private ?Campus $campus;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDateLimitForRegistration(): ?\DateTimeInterface
    {
        return $this->dateLimitForRegistration;
    }

    public function setDateLimitForRegistration(\DateTimeInterface $dateLimitForRegistration): self
    {
        $this->dateLimitForRegistration = $dateLimitForRegistration;

        return $this;
    }

    public function getMaxRegistrationNumber(): ?int
    {
        return $this->maxRegistrationNumber;
    }

    public function setMaxRegistrationNumber(int $maxRegistrationNumber): self
    {
        $this->maxRegistrationNumber = $maxRegistrationNumber;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOrganiser(): ?User
    {
        return $this->organiser;
    }

    public function setOrganiser(?User $organiser): self
    {
        $this->organiser = $organiser;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @param ArrayCollection $participants
     */
    public function setParticipants(ArrayCollection $participants): void
    {
        $this->participants = $participants;
    }
}
