<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $firstLine = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $secondLine = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $town = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank]
    private ?string $postcode = null;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $belongsTo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Country $country = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstLine(): ?string
    {
        return $this->firstLine;
    }

    public function setFirstLine(string $firstLine): static
    {
        $this->firstLine = $firstLine;

        return $this;
    }

    public function getSecondLine(): ?string
    {
        return $this->secondLine;
    }

    public function setSecondLine(string $secondLine): static
    {
        $this->secondLine = $secondLine;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(string $town): static
    {
        $this->town = $town;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): static
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getBelongsTo(): ?User
    {
        return $this->belongsTo;
    }

    public function setBelongsTo(?User $belongsTo): static
    {
        $this->belongsTo = $belongsTo;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }
}
