<?php

namespace App\MessageHandler;

use App\Entity\MediaPod;
use App\Protobuf\MediaPodStatus;
use App\Protobuf\SubtitleMergerToApi;
use App\Protobuf\SubtitleTransformerToApi;
use App\Repository\MediaPodRepository;
use App\Service\ProtobufService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SubtitleTransformerToApiMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private MediaPodRepository $mediaPodRepository,
        private MessageBusInterface $messageBus,
        private ProtobufService $protobufService,
    ) {
    }

    public function __invoke(SubtitleTransformerToApi $subtitleTransformerToApi): void
    {
        $this->logger->info('############################################################################################################################################');
        $this->logger->info(sprintf('Received SubtitleTransformerToApi message with mediaPod uuid : %s', $subtitleTransformerToApi->getMediaPod()->getUuid()));

        $mediaPod = $this->mediaPodRepository->findOneBy([
            'uuid' => $subtitleTransformerToApi->getMediaPod()->getUuid(),
        ]);

        if (!$mediaPod instanceof MediaPod) {
            return;
        }

        $status = $subtitleTransformerToApi->getMediaPod()->getStatus();

        if (MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_COMPLETE) !== $status) {
            $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status],
                'status' => $status,
            ]);

            return;
        }

        $mediaPod->getOriginalVideo()->setAss($subtitleTransformerToApi->getMediaPod()->getOriginalVideo()->getAss());

        $mediaPod = $this->mediaPodRepository->update($mediaPod, [
            'statuses' => [$status, MediaPodStatus::name(MediaPodStatus::SUBTITLE_INCRUSTATOR_PENDING)],
            'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_INCRUSTATOR_PENDING),
        ]);

        $this->protobufService->toSubtitleIncrustator($subtitleTransformerToApi);
    }
}
