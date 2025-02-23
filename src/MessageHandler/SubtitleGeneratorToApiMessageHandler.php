<?php

namespace App\MessageHandler;

use App\Entity\MediaPod;
use App\Protobuf\MediaPodStatus;
use App\Protobuf\SubtitleGeneratorToApi;
use App\Repository\MediaPodRepository;
use App\Service\MediaPodOrchestrator;
use App\Service\ProtobufService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SubtitleGeneratorToApiMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private MediaPodRepository $mediaPodRepository,
        private MediaPodOrchestrator $mediaPodOrchestrator
    ) {
    }

    public function __invoke(SubtitleGeneratorToApi $subtitleGeneratorToApi): void
    {
        $this->logger->info('############################################################################################################################################');
        $this->logger->info(sprintf('Received from SubtitleGenerator with mediaPod uuid : %s', $subtitleGeneratorToApi->getMediaPod()->getUuid()));

        $mediaPod = $this->mediaPodRepository->findOneBy([
            'uuid' => $subtitleGeneratorToApi->getMediaPod()->getUuid(),
        ]);

        if (!$mediaPod instanceof MediaPod) {
            return;
        }

        $status = $subtitleGeneratorToApi->getMediaPod()->getStatus();

        if (MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_COMPLETE) !== $status) {
            $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status],
                'status' => $status,
            ]);

            return;
        }

        $mediaPod->getOriginalVideo()->setSubtitles([]);
        foreach ($subtitleGeneratorToApi->getMediaPod()->getOriginalVideo()->getSubtitles()->getIterator() as $iterator) {
            $mediaPod->getOriginalVideo()->addSubtitles($iterator);
        }
        
        $this->mediaPodOrchestrator->dispatch($subtitleGeneratorToApi->getMediaPod(), $mediaPod, $status);
    }
}