<?php

namespace App\MessageHandler;

use App\Entity\MediaPod;
use App\Protobuf\MediaPodStatus;
use App\Protobuf\SoundExtractorToApi;
use App\Repository\MediaPodRepository;
use App\Service\ProtobufService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SoundExtractorToApiMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private MediaPodRepository $mediaPodRepository,
        private MessageBusInterface $messageBus,
        private ProtobufService $protobufService,
    ) {
    }

    public function __invoke(SoundExtractorToApi $soundExtractorToApi): void
    {
        $this->logger->info('############################################################################################################################################');
        $this->logger->info(sprintf('Received SoundExtractorToApi message with mediaPod uuid : %s', $soundExtractorToApi->getMediaPod()->getUuid()));

        $mediaPod = $this->mediaPodRepository->findOneBy([
            'uuid' => $soundExtractorToApi->getMediaPod()->getUuid(),
        ]);

        if (!$mediaPod instanceof MediaPod) {
            return;
        }

        $status = $soundExtractorToApi->getMediaPod()->getStatus();

        if (MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_COMPLETE) !== $status) {
            $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status],
                'status' => $status,
            ]);

            return;
        }

        $mediaPod->getOriginalVideo()->setAudios([]);
        foreach ($soundExtractorToApi->getMediaPod()->getOriginalVideo()->getAudios()->getIterator() as $iterator) {
            $mediaPod->getOriginalVideo()->addAudios($iterator);
        }

        $mediaPod->getOriginalVideo()->setLength($soundExtractorToApi->getMediaPod()->getOriginalVideo()->getLength());

        $mediaPod = $this->mediaPodRepository->update($mediaPod, [
            'statuses' => [$status, MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_PENDING)],
            'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_PENDING),
        ]);

        $this->protobufService->toSubtitleGenerator($soundExtractorToApi);
    }
}
