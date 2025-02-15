<?php

namespace App\MessageHandler;

use App\Entity\MediaPod;
use App\Enum\MediaPodStatus;
use App\Protobuf\SoundExtractorToApi;
use App\Protobuf\SubtitleGeneratorToApi;
use App\Repository\MediaPodRepository;
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
        private MessageBusInterface $messageBus,
        private ProtobufService $protobufService
    ) {
    }

    public function __invoke(SubtitleGeneratorToApi $subtitleGeneratorToApi): void
    {
        $this->logger->info('############################################################################################################################################');
        $this->logger->info(sprintf('Received SoundExtractorToApi message with mediaPod uuid : %s', $subtitleGeneratorToApi->getMediaPod()->getUuid()));

        $mediaPod = $this->mediaPodRepository->findOneBy([
            'uuid' => $subtitleGeneratorToApi->getMediaPod()->getUuid(),
        ]);

        if (!$mediaPod instanceof MediaPod) {
            return;
        }

        $status = $subtitleGeneratorToApi->getMediaPod()->getStatus();
        
        if ($status !== MediaPodStatus::SUBTITLE_GENERATOR_COMPLETE->getValue()) {
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

        $mediaPod = $this->mediaPodRepository->update($mediaPod, [
            'statuses' => [$status, MediaPodStatus::SUBTITLE_MERGER_PENDING->getValue()],
            'status' => MediaPodStatus::SUBTITLE_MERGER_PENDING->getValue(),
        ]);

        $this->protobufService->toSubtitleMerger($subtitleGeneratorToApi);
    }
}
