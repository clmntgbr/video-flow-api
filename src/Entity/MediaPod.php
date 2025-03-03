<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\ApiResource\UploadVideoAction;
use App\Entity\Traits\UuidTrait;
use App\Protobuf\MediaPodStatus;
use App\Repository\MediaPodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MediaPodRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['media-pods:get', 'default']],
        ),
        new Get(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['media-pods:get', 'default']],
        ),
        new Post(
            uriTemplate: '/media-pods/video/upload',
            controller: UploadVideoAction::class,
            normalizationContext: ['groups' => ['media-pods:get']],
        ),
    ]
)]
class MediaPod
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'videoHubs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?string $videoName = null;

    #[ORM\OneToOne(targetEntity: Video::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'orginal_video_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['media-pods:get'])]
    private ?Video $originalVideo = null;

    #[ORM\OneToOne(targetEntity: Video::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'processed_video_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?Video $processedVideo = null;

    #[ManyToMany(targetEntity: Video::class, cascade: ['persist', 'remove'])]
    #[JoinTable(name: 'media_pod_final_videos')]
    #[Groups(['media-pods:get'])]
    private Collection $finalVideo;

    #[ORM\OneToOne(targetEntity: Configuration::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'configuration_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['media-pods:get'])]
    private ?Configuration $configuration = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['media-pods:get'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['media-pods:get'])]
    private array $statuses = [];

    public function __construct()
    {
        $this->status = MediaPodStatus::name(MediaPodStatus::UPLOAD_COMPLETE);
        $this->finalVideo = new ArrayCollection();
    }

    public function getOriginalVideo(): ?Video
    {
        return $this->originalVideo;
    }

    public function setOriginalVideo(Video $originalVideo): static
    {
        $this->originalVideo = $originalVideo;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    #[Groups(['media-pods:get'])]
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[Groups(['media-pods:get'])]
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getVideoName(): ?string
    {
        return $this->videoName;
    }

    public function setVideoName(?string $videoName): static
    {
        $this->videoName = $videoName;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): static
    {
        $this->statuses = array_merge($this->statuses, $statuses);

        return $this;
    }

    public function initStatuses(): static
    {
        $this->statuses = [];

        return $this;
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Configuration $configuration): static
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getProcessedVideo(): ?Video
    {
        return $this->processedVideo;
    }

    public function setProcessedVideo(?Video $processedVideo): static
    {
        $this->processedVideo = $processedVideo;

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getFinalVideo(): Collection
    {
        return $this->finalVideo;
    }

    public function addFinalVideo(Video $finalVideo): static
    {
        if (!$this->finalVideo->contains($finalVideo)) {
            $this->finalVideo->add($finalVideo);
        }

        return $this;
    }

    public function removeFinalVideo(Video $finalVideo): static
    {
        $this->finalVideo->removeElement($finalVideo);

        return $this;
    }
}
