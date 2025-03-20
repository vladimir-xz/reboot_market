<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 510)]
    private ?string $name = null;

    #[ORM\Column(length: 16)]
    private ?string $condition = null;

    #[ORM\Column]
    private ?int $weight = null;

    #[ORM\Column(nullable: true)]
    private ?int $length = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    /**
     * @var Collection<int, Specification>
     */
    #[ORM\ManyToMany(targetEntity: Specification::class, mappedBy: 'products')]
    private Collection $specifications;

    #[ORM\Column(length: 50)]
    private ?string $brand = null;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(
        targetEntity: Image::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $images;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class)]
    private Collection $related;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(inversedBy: 'product', cascade: ['persist', 'remove'])]
    private ?ProductDescription $description = null;

    public function __construct()
    {
        $this->specifications = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->related = new ArrayCollection();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): static
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
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

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(string $condition): static
    {
        $this->condition = $condition;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category_id): static
    {
        $this->category = $category_id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Specification>
     */
    public function getSpecifications(): Collection
    {
        return $this->specifications;
    }

    public function addSpecification(Specification $specification): static
    {
        if (!$this->specifications->contains($specification)) {
            $this->specifications->add($specification);
            $specification->addProduct($this);
        }

        return $this;
    }

    public function removeSpecification(Specification $specification): static
    {
        if ($this->specifications->removeElement($specification)) {
            $specification->removeProduct($this);
        }

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function getMainImagePath(): string
    {
        foreach ($this->images as $image) {
            if ($image->isMain()) {
                return $image->getPath();
            }
        }

        return $this->images[0]->getPath();
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getRelated(): Collection
    {
        return $this->related;
    }

    public function addRelated(self $related): static
    {
        if (!$this->related->contains($related)) {
            $this->related->add($related);
        }

        return $this;
    }

    public function removeRelated(self $related): static
    {
        $this->related->removeElement($related);

        return $this;
    }

    public function getDescription(): ?ProductDescription
    {
        return $this->description;
    }

    public function setDescription(ProductDescription $description): static
    {
        // set the owning side of the relation if necessary
        if ($description->getProduct() !== $this) {
            $description->setProduct($this);
        }

        $this->description = $description;

        return $this;
    }
}
