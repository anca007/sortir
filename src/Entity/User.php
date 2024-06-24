<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email', message: 'Pas possible bonhomme')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $firstname;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastname;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $phone;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Campus::class, inversedBy: 'users')]
    private ?Campus $campus;

    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'organiser')]
    private Collection $organiserActivities;

    #[ORM\ManyToMany(targetEntity: Activity::class, mappedBy: 'participants')]
    private Collection $participatingActivities;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photo;

    public function __construct()
    {
        $this->organiserActivities = new ArrayCollection();
        $this->participatingActivities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array   {

        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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
     * @return Collection|Activity[]
     */
    public function getorganiserActivities(): Collection
    {
        return $this->organiserActivities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->organiserActivities->contains($activity)) {
            $this->organiserActivities[] = $activity;
            $activity->setOrganiser($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->organiserActivities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getOrganiser() === $this) {
                $activity->setOrganiser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Activity[]
     */
    public function getParticipatingActivities(): Collection
    {
        return $this->participatingActivities;
    }

    public function addParticipatingActivity(Activity $participatingActivity): self
    {
        if (!$this->participatingActivities->contains($participatingActivity)) {
            $this->participatingActivities[] = $participatingActivity;
            $participatingActivity->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipatingActivity(Activity $participatingActivity): self
    {
        if ($this->participatingActivities->removeElement($participatingActivity)) {
            $participatingActivity->removeParticipant($this);
        }

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
