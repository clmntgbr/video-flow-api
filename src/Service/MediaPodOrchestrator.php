<?php

namespace App\Service;

use App\Entity\MediaPod;
use App\Entity\User;
use App\Protobuf\ApiToSoundExtractor;
use App\Protobuf\ApiToSubtitleGenerator;
use App\Protobuf\ApiToSubtitleIncrustator;
use App\Protobuf\ApiToSubtitleMerger;
use App\Protobuf\ApiToSubtitleTransformer;
use App\Protobuf\ApiToVideoFormatter;
use App\Protobuf\MediaPod as ProtoMediaPod;
use App\Protobuf\MediaPodStatus;
use App\Protobuf\Preset as ProtoPreset;
use App\Protobuf\SoundExtractorToApi;
use App\Protobuf\SubtitleGeneratorToApi;
use App\Protobuf\SubtitleMergerToApi;
use App\Protobuf\SubtitleTransformerToApi;
use App\Protobuf\Video as ProtoVideo;
use App\Protobuf\VideoFormatterToApi;
use App\Repository\MediaPodRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

class MediaPodOrchestrator
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private ProtobufService $protobufService,
        private MediaPodRepository $mediaPodRepository,
    ) {
    }

    public function dispatch(ProtoMediaPod $protoMediaPod, MediaPod $mediaPod, string $status)
    {
        if ($status === MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_COMPLETE)) {
            /** @var MediaPod $mediaPod */
            $mediaPod = $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status, MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_PENDING)],
                'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_PENDING),
            ]);
            $this->protobufService->toSubtitleGenerator($protoMediaPod);
            return;
        }

        if ($status === MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_COMPLETE)) {
            /** @var MediaPod $mediaPod */
            $mediaPod = $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status, MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_PENDING)],
                'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_PENDING),
            ]);
            $this->protobufService->toSubtitleMerger($protoMediaPod);
            return;
        }

        if ($status === MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_COMPLETE)) {
            /** @var MediaPod $mediaPod */
            $mediaPod = $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status, MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_PENDING)],
                'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_PENDING),
            ]);
            $this->protobufService->toSubtitleTransformer($protoMediaPod);
            return;
        }

        if ($status === MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_COMPLETE)) {
            /** @var MediaPod $mediaPod */
            $mediaPod = $this->mediaPodRepository->update($mediaPod, [
                'statuses' => [$status, MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_PENDING)],
                'status' => MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_PENDING),
            ]);
            $this->protobufService->toVideoFormatter($protoMediaPod);
            return;
        }
    }
}