<?php

namespace App\MessageHandler;

use App\Entity\MediaPod;
use App\Protobuf\MediaPodStatus;
use App\Protobuf\SubtitleMergerToApi;
use App\Repository\MediaPodRepository;
use App\Service\ProtobufService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SubtitleMergerToApiMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private MediaPodRepository $mediaPodRepository,
        private MessageBusInterface $messageBus,
        private ProtobufService $protobufService,
    ) {
    }

    public function __invoke(SubtitleMergerToApi $subtitleMergerToApi): void
    {
        $this->logger->info('############################################################################################################################################');
        $this->logger->info(sprintf('Received SubtitleMergerToApi message with mediaPod uuid : %s', $subtitleMergerToApi->getMediaPod()->getUuid()));

        $mediaPod = $this->mediaPodRepository->findOneBy([
            'uuid' => $subtitleMergerToApi->getMediaPod()->getUuid(),
        ]);

        if (!$mediaPod instanceof MediaPod) {
            return;
        }

        $status = $subtitleMergerToApi->getMediaPod()->getStatus();

        if (MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_COMPLETE) !== $status) {
            $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status],
                'status' => $status,
            ]);

            return;
        }

        $mediaPod->getOriginalVideo()->setSubtitle($subtitleMergerToApi->getMediaPod()->getOriginalVideo()->getSubtitle());

        $mediaPod = $this->mediaPodRepository->update($mediaPod, [
            'statuses' => [$status, MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_PENDING)],
            'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_PENDING),
        ]);

        $this->protobufService->toSubtitleTransformer($subtitleMergerToApi);
    }
}
