<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['location'])]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['location'])]
    private ?string $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['location'])]
    #[Assert\NotBlank]
    private ?string $street;

    #[ORM\Column(type: 'float')]
    #[Groups(['location'])]
    private ?float $latitude;

    #[ORM\Column(type: 'float')]
    #[Groups(['location'])]
    private ?float $longitude;

    
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: City::class, inversedBy: 'locations')]
    #[Groups(['location'])]
    private ?City $city;

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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getStreet() . " " . $this->getCity()->getZipcode() . " " . $this->getCity()->getName();
    }
}
