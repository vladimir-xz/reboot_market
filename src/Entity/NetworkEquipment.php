<?php

namespace App\Entity;

use App\Repository\NetworkEquipmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NetworkEquipmentRepository::class)]
class NetworkEquipment extends Product
{
    #[ORM\Column(nullable: true)]
    private ?int $length = null;

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): static
    {
        $this->length = $length;

        return $this;
    }
}
