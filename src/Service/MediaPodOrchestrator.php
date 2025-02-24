<?php

namespace App\Service;

use App\Entity\MediaPod;
use App\Protobuf\MediaPod as ProtoMediaPod;
use App\Protobuf\MediaPodStatus;
use App\Repository\MediaPodRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class MediaPodOrchestrator
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private ProtobufService $protobufService,
        private MediaPodRepository $mediaPodRepository,
    ) {
    }

    public function dispatch(ProtoMediaPod $protoMediaPod, MediaPod $mediaPod, string $status): void
    {
        if (in_array($status, [
            MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_ERROR),
            MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_ERROR),
            MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_ERROR),
            MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_ERROR),
            MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_ERROR),
        ])) {
            return;
        }

        if ($status === MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_COMPLETE)) {
            /** @var MediaPod $mediaPod */
            $mediaPod = $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_PENDING)],
                'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_PENDING),
            ]);
            $this->protobufService->toSubtitleGenerator($protoMediaPod);

            return;
        }

        if ($status === MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_COMPLETE)) {
            /** @var MediaPod $mediaPod */
            $mediaPod = $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_PENDING)],
                'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_PENDING),
            ]);
            $this->protobufService->toSubtitleMerger($protoMediaPod);

            return;
        }

        if ($status === MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_COMPLETE)) {
            /** @var MediaPod $mediaPod */
            $mediaPod = $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_PENDING)],
                'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_PENDING),
            ]);
            $this->protobufService->toSubtitleTransformer($protoMediaPod);

            return;
        }

        if ($status === MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_COMPLETE)) {
            /** @var MediaPod $mediaPod */
            $mediaPod = $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_PENDING)],
                'status' => MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_PENDING),
            ]);
            $this->protobufService->toVideoFormatter($protoMediaPod);

            return;
        }
    }
}
