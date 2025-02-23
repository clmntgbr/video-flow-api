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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

class ProtobufService
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function toSoundExtractor(UploadedFile $uploadedFile, MediaPod $mediaPod, User $user, string $fileName): void
    {
        $protoVideo = new ProtoVideo();
        $protoVideo->setName($fileName);
        $protoVideo->setMimeType($uploadedFile->getMimeType());
        $protoVideo->setSize($uploadedFile->getSize());

        $protoPreset = new ProtoPreset();
        $protoPreset->setSubtitleFont($mediaPod->getPreset()->getSubtitleFont());
        $protoPreset->setSubtitleSize($mediaPod->getPreset()->getSubtitleSize());
        $protoPreset->setSubtitleColor($mediaPod->getPreset()->getSubtitleColor());
        $protoPreset->setSubtitleBold($mediaPod->getPreset()->getSubtitleBold());
        $protoPreset->setSubtitleItalic($mediaPod->getPreset()->getSubtitleItalic());
        $protoPreset->setSubtitleUnderline($mediaPod->getPreset()->getSubtitleUnderline());
        $protoPreset->setSubtitleOutlineColor($mediaPod->getPreset()->getSubtitleOutlineColor());
        $protoPreset->setSubtitleOutlineThickness($mediaPod->getPreset()->getSubtitleOutlineThickness());
        $protoPreset->setSubtitleShadowColor($mediaPod->getPreset()->getSubtitleShadowColor());
        $protoPreset->setSubtitleShadow($mediaPod->getPreset()->getSubtitleShadow());

        $protoMediaPod = new ProtoMediaPod();
        $protoMediaPod->setUuid($mediaPod->getUuid());
        $protoMediaPod->setUserUuid($user->getUuid());
        $protoMediaPod->setOriginalVideo($protoVideo);
        $protoMediaPod->setPreset($protoPreset);
        $protoMediaPod->setStatus(MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_PENDING));

        $apiToSoundExtractor = new ApiToSoundExtractor();
        $apiToSoundExtractor->setMediaPod($protoMediaPod);

        $this->messageBus->dispatch($apiToSoundExtractor, [
            new AmqpStamp('api_to_sound_extractor', 0, []),
        ]);
    }

    public function toSubtitleGenerator(ProtoMediaPod $mediaPod): void
    {
        $protobuf = new ApiToSubtitleGenerator();
        $protobuf->setMediaPod($mediaPod);

        $this->messageBus->dispatch($protobuf, [
            new AmqpStamp('api_to_subtitle_generator', 0, []),
        ]);
    }

    public function toSubtitleMerger(ProtoMediaPod $mediaPod): void
    {
        $protobuf = new ApiToSubtitleMerger();
        $protobuf->setMediaPod($mediaPod);

        $this->messageBus->dispatch($protobuf, [
            new AmqpStamp('api_to_subtitle_merger', 0, []),
        ]);
    }

    public function toSubtitleIncrustator(ProtoMediaPod $mediaPod): void
    {
        $protobuf = new ApiToSubtitleIncrustator();
        $protobuf->setMediaPod($mediaPod);

        $this->messageBus->dispatch($protobuf, [
            new AmqpStamp('api_to_subtitle_incrustator', 0, []),
        ]);
    }

    public function toVideoFormatter(ProtoMediaPod $mediaPod): void
    {
        $protobuf = new ApiToVideoFormatter();
        $protobuf->setMediaPod($mediaPod);

        $this->messageBus->dispatch($protobuf, [
            new AmqpStamp('api_to_video_formatter', 0, []),
        ]);
    }

    public function toSubtitleTransformer(ProtoMediaPod $mediaPod): void
    {
        $protobuf = new ApiToSubtitleTransformer();
        $protobuf->setMediaPod($mediaPod);

        $this->messageBus->dispatch($protobuf, [
            new AmqpStamp('api_to_subtitle_transformer', 0, []),
        ]);
    }
}