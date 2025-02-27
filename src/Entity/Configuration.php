<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\UuidTrait;
use App\Protobuf\ConfigurationSubtitleFont;
use App\Protobuf\ConfigurationSubtitleOutlineThickness;
use App\Protobuf\ConfigurationSubtitleShadow;
use App\Protobuf\VideoFormatStyle;
use App\Repository\ConfigurationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
#[ApiResource]
class Configuration
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
    private ?string $subtitleBold = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleItalic = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $subtitleUnderline = null;

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

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['media-pods:get'])]
    private ?string $format = null;

    public function __construct()
    {
        $this->format = VideoFormatStyle::name(VideoFormatStyle::ORIGINAL);
        $this->subtitleFont = ConfigurationSubtitleFont::name(ConfigurationSubtitleFont::ARIAL);
        $this->subtitleShadow = (string) ConfigurationSubtitleShadow::SHADOW_MEDIUM;
        $this->subtitleShadowColor = '#000000';
        $this->subtitleOutlineThickness = (string) ConfigurationSubtitleOutlineThickness::OUTLINE_MEDIUM;
        $this->subtitleOutlineColor = '#000000';
        $this->subtitleBold = '0';
        $this->subtitleItalic = '0';
        $this->subtitleUnderline = '0';
        $this->subtitleColor = '#FFFFFF';
        $this->subtitleSize = '16';
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

    public function getSubtitleBold(): ?string
    {
        return $this->subtitleBold;
    }

    public function setSubtitleBold(?string $subtitleBold): static
    {
        $this->subtitleBold = $subtitleBold;

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

    public function getSubtitleItalic(): ?string
    {
        return $this->subtitleItalic;
    }

    public function setSubtitleItalic(?string $subtitleItalic): static
    {
        $this->subtitleItalic = $subtitleItalic;

        return $this;
    }

    public function getSubtitleUnderline(): ?string
    {
        return $this->subtitleUnderline;
    }

    public function setSubtitleUnderline(?string $subtitleUnderline): static
    {
        $this->subtitleUnderline = $subtitleUnderline;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;

        return $this;
    }
}
