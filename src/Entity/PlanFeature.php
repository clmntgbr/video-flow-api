<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\UuidTrait;
use App\Repository\PlanFeatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlanFeatureRepository::class)]
#[ApiResource(
    operations: []
)]
class PlanFeature
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Column(Types::STRING)]
    #[Groups(['plan-features:get'])]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['plan-features:get'])]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['plan-features:get'])]
    private bool $enabled;

    #[ORM\ManyToOne(targetEntity: Plan::class, inversedBy: 'features')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Plan $plan = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4()->toString();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }
}
