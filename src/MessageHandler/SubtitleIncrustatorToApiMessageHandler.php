<?php

namespace App\MessageHandler;

use App\Entity\MediaPod;
use App\Entity\Video;
use App\Protobuf\MediaPodStatus;
use App\Protobuf\SubtitleIncrustatorToApi;
use App\Protobuf\SubtitleTransformerToApi;
use App\Repository\MediaPodRepository;
use App\Service\ProtobufService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SubtitleIncrustatorToApiMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private MediaPodRepository $mediaPodRepository,
        private MessageBusInterface $messageBus,
        private ProtobufService $protobufService,
    ) {
    }

    public function __invoke(SubtitleIncrustatorToApi $subtitleIncrustatorToApi): void
    {
        $this->logger->info('############################################################################################################################################');
        $this->logger->info(sprintf('Received from SubtitleIncrustator with mediaPod uuid : %s', $subtitleIncrustatorToApi->getMediaPod()->getUuid()));
        
        $mediaPod = $this->mediaPodRepository->findOneBy([
            'uuid' => $subtitleIncrustatorToApi->getMediaPod()->getUuid(),
        ]);

        if (!$mediaPod instanceof MediaPod) {
            return;
        }

        $status = $subtitleIncrustatorToApi->getMediaPod()->getStatus();

        if (MediaPodStatus::name(MediaPodStatus::SUBTITLE_INCRUSTATOR_COMPLETE) !== $status) {
            $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status],
                'status' => $status,
            ]);

            return;
        }

        $video = new Video();
        $video->setName($subtitleIncrustatorToApi->getMediaPod()->getProcessedVideo()->getName());
        $video->setOriginalName($mediaPod->getOriginalVideo()->getOriginalName());
        $video->setMimeType($subtitleIncrustatorToApi->getMediaPod()->getProcessedVideo()->getMimeType());
        $video->setSize($subtitleIncrustatorToApi->getMediaPod()->getProcessedVideo()->getSize());
        $video->setLength($mediaPod->getOriginalVideo()->getLength());

        $mediaPod = $this->mediaPodRepository->update($mediaPod, [
            'statuses' => [$status],
            'status' => $status,
            'processedVideo' => $video,
        ]);
    }
}