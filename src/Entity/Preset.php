<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Repository\PresetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PresetRepository::class)]
#[ApiResource]
class Preset
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleFont = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleSize = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleColor = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $videoFormat = null;

    public function __construct()
    {
        $this->initializeUuid();
    }

    public function getSubtitleFont(): ?string
    {
        return $this->subtitleFont;
    }

    public function setSubtitleFont(?string $subtitleFont): static
    {
        $this->subtitleFont = $subtitleFont;

        return $this;
    }

    public function getSubtitleSize(): ?string
    {
        return $this->subtitleSize;
    }

    public function setSubtitleSize(?string $subtitleSize): static
    {
        $this->subtitleSize = $subtitleSize;

        return $this;
    }

    public function getSubtitleColor(): ?string
    {
        return $this->subtitleColor;
    }

    public function setSubtitleColor(?string $subtitleColor): static
    {
        $this->subtitleColor = $subtitleColor;

        return $this;
    }

    public function getVideoFormat(): ?string
    {
        return $this->videoFormat;
    }

    public function setVideoFormat(?string $videoFormat): static
    {
        $this->videoFormat = $videoFormat;

        return $this;
    }
}
