<?php

namespace App\Entity;

use App\Repository\ShippingMethodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShippingMethodRepository::class)]
class ShippingMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, Country>
     */
    #[ORM\ManyToMany(targetEntity: Country::class, inversedBy: 'shippingMethods')]
    private Collection $country;

    /**
     * @var Collection<int, FreightRate>
     */
    #[ORM\OneToMany(targetEntity: FreightRate::class, mappedBy: 'shippingMethod', orphanRemoval: true)]
    private Collection $rates;

    public function __construct()
    {
        $this->country = new ArrayCollection();
        $this->rates = new ArrayCollection();
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
     * @return Collection<int, Country>
     */
    public function getCountry(): Collection
    {
        return $this->country;
    }

    public function addCountry(Country $country): static
    {
        if (!$this->country->contains($country)) {
            $this->country->add($country);
        }

        return $this;
    }

    public function removeCountry(Country $country): static
    {
        $this->country->removeElement($country);

        return $this;
    }

    /**
     * @return Collection<int, FreightRate>
     */
    public function getRates(): Collection
    {
        return $this->rates;
    }

    public function addRate(FreightRate $rate): static
    {
        if (!$this->rates->contains($rate)) {
            $this->rates->add($rate);
            $rate->setShippingMethod($this);
        }

        return $this;
    }

    public function removeRate(FreightRate $rate): static
    {
        if ($this->rates->removeElement($rate)) {
            // set the owning side to null (unless already changed)
            if ($rate->getShippingMethod() === $this) {
                $rate->setShippingMethod(null);
            }
        }

        return $this;
    }
}
