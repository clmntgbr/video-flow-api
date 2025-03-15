<?php

namespace App\EventListener;

use App\Entity\MediaPod;
use App\Entity\User;
use App\Protobuf\MediaPodStatus;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDoctrineListener(event: Events::postLoad, priority: 0, connection: 'default')]
readonly class MediaPodEvent
{
    public function __construct(
    ) {
    }

    public function postLoad(PostLoadEventArgs $postLoadEventArgs): void
    {
        $entity = $postLoadEventArgs->getObject();
        if (!$entity instanceof MediaPod) {
            return;
        }

        $progressMap = $this->getMediaPodStatus();
        $maxProgress = max($progressMap);
        $currentProgress = $progressMap[$entity->getStatus()] ?? 0;

        $entity->setPercent(round(($currentProgress / $maxProgress) * 100));
    }

    private function getMediaPodStatus() {
        return [
            MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_PENDING) => 1,
            MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_COMPLETE) => 2,

            MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_PENDING) => 3,
            MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_COMPLETE) => 4,

            MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_PENDING) => 5,
            MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_COMPLETE) => 6,

            MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_PENDING) => 7,
            MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_COMPLETE) => 8,

            MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_PENDING) => 9,
            MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_COMPLETE) => 10,

            MediaPodStatus::name(MediaPodStatus::SUBTITLE_INCRUSTATOR_PENDING) => 11,
            MediaPodStatus::name(MediaPodStatus::SUBTITLE_INCRUSTATOR_COMPLETE) => 12,

            MediaPodStatus::name(MediaPodStatus::VIDEO_SPLITTER_PENDING) => 13,
            MediaPodStatus::name(MediaPodStatus::VIDEO_SPLITTER_COMPLETE) => 14,

            MediaPodStatus::name(MediaPodStatus::VIDEO_INCRUSTATOR_PENDING) => 15,
            MediaPodStatus::name(MediaPodStatus::VIDEO_INCRUSTATOR_COMPLETE) => 16,
        ];
    }
}
