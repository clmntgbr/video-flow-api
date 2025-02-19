<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Protobuf\PresetSubtitleFont;
use App\Protobuf\PresetSubtitleShadow;
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
    private ?string $subtitleBackground = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleOutlineColor = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleOutlineThickness = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleShadow = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleShadowColor = null;

    public function __construct()
    {
        $this->subtitleShadow = PresetSubtitleShadow::name(PresetSubtitleShadow::NONE);
        $this->subtitleShadowColor = '#000000';
        $this->subtitleOutlineThickness = '0';
        $this->subtitleOutlineColor = '#000000';
        $this->subtitleBackground = '#000000';
        $this->subtitleColor = '#FFFFFF';
        $this->subtitleSize = '24';
        $this->subtitleFont = PresetSubtitleFont::name(PresetSubtitleFont::ARIAL);
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

    public function getSubtitleBackground(): ?string
    {
        return $this->subtitleBackground;
    }

    public function setSubtitleBackground(?string $subtitleBackground): static
    {
        $this->subtitleBackground = $subtitleBackground;

        return $this;
    }

    public function getSubtitleOutlineColor(): ?string
    {
        return $this->subtitleOutlineColor;
    }

    public function setSubtitleOutlineColor(?string $subtitleOutlineColor): static
    {
        $this->subtitleOutlineColor = $subtitleOutlineColor;

        return $this;
    }

    public function getSubtitleOutlineThickness(): ?string
    {
        return $this->subtitleOutlineThickness;
    }

    public function setSubtitleOutlineThickness(?string $subtitleOutlineThickness): static
    {
        $this->subtitleOutlineThickness = $subtitleOutlineThickness;

        return $this;
    }

    public function getSubtitleShadow(): ?string
    {
        return $this->subtitleShadow;
    }

    public function setSubtitleShadow(?string $subtitleShadow): static
    {
        $this->subtitleShadow = $subtitleShadow;

        return $this;
    }

    public function getSubtitleShadowColor(): ?string
    {
        return $this->subtitleShadowColor;
    }

    public function setSubtitleShadowColor(?string $subtitleShadowColor): static
    {
        $this->subtitleShadowColor = $subtitleShadowColor;

        return $this;
    }
}
