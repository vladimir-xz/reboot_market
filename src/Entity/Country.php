<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ShippingMethod>
     */
    #[ORM\ManyToMany(targetEntity: ShippingMethod::class, mappedBy: 'country')]
    private Collection $shippingMethods;

    /**
     * @var Collection<int, FreightRate>
     */
    #[ORM\OneToMany(targetEntity: FreightRate::class, mappedBy: 'country', orphanRemoval: true)]
    private Collection $freightRates;

    public function __construct()
    {
        $this->shippingMethods = new ArrayCollection();
        $this->freightRates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ShippingMethod>
     */
    public function getShippingMethods(): Collection
    {
        return $this->shippingMethods;
    }

    public function addShippingMethod(ShippingMethod $shippingMethod): static
    {
        if (!$this->shippingMethods->contains($shippingMethod)) {
            $this->shippingMethods->add($shippingMethod);
            $shippingMethod->addCountry($this);
        }

        return $this;
    }

    public function removeShippingMethod(ShippingMethod $shippingMethod): static
    {
        if ($this->shippingMethods->removeElement($shippingMethod)) {
            $shippingMethod->removeCountry($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, FreightRate>
     */
    public function getFreightRates(): Collection
    {
        return $this->freightRates;
    }

    public function addFreightRate(FreightRate $freightRate): static
    {
        if (!$this->freightRates->contains($freightRate)) {
            $this->freightRates->add($freightRate);
            $freightRate->setCountry($this);
        }

        return $this;
    }

    public function removeFreightRate(FreightRate $freightRate): static
    {
        if ($this->freightRates->removeElement($freightRate)) {
            // set the owning side to null (unless already changed)
            if ($freightRate->getCountry() === $this) {
                $freightRate->setCountry(null);
            }
        }

        return $this;
    }
}
