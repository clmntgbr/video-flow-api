<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\UuidTrait;
use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['plans:get', 'plan-features:get', 'default']],
        ),
        new GetCollection(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['plans:get', 'plan-features:get', 'default']],
        ),
    ]
)]
class Plan
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Column(Types::STRING)]
    #[Groups(['plans:get'])]
    private string $name;

    #[ORM\Column(Types::FLOAT)]
    #[Groups(['plans:get'])]
    private float $price;

    #[ORM\OneToMany(mappedBy: 'plan', targetEntity: PlanFeature::class, cascade: ['persist', 'remove'])]
    #[Groups(['plan-features:get'])]
    private Collection $features;

    public function __construct()
    {
        $this->uuid = Uuid::v4()->toString();
        $this->features = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, PlanFeature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(PlanFeature $feature): static
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setPlan($this);
        }

        return $this;
    }

    public function removeFeature(PlanFeature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            // set the owning side to null (unless already changed)
            if ($feature->getPlan() === $this) {
                $feature->setPlan(null);
            }
        }

        return $this;
    }
}
