<?php

namespace App\Service;

use App\Entity\MediaPod;
use App\Entity\User;
use App\Enum\MediaPodStatus;
use App\Protobuf\ApiToSoundExtractor;
use App\Protobuf\ApiToSubtitleGenerator;
use App\Protobuf\MediaPod as ProtoMediaPod;
use App\Protobuf\SoundExtractorToApi;
use App\Protobuf\Video as ProtoVideo;
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

        $protoMediaPod = new ProtoMediaPod();
        $protoMediaPod->setUuid($mediaPod->getUuid());
        $protoMediaPod->setUserUuid($user->getUuid());
        $protoMediaPod->setOriginalVideo($protoVideo);
        $protoMediaPod->setStatus(MediaPodStatus::SOUND_EXTRACTOR_PENDING->getValue());

        $apiToSoundExtractor = new ApiToSoundExtractor();
        $apiToSoundExtractor->setMediaPod($protoMediaPod);

        $this->messageBus->dispatch($apiToSoundExtractor, [
            new AmqpStamp('api_to_sound_extractor', 0, []),
        ]);
    }

    public function toSubtitleGenerator(SoundExtractorToApi $soundExtractorToApi): void
    {
        $apiToSubtitleGenerator = new ApiToSubtitleGenerator();
        $mediaPod = $soundExtractorToApi->getMediaPod();
        $apiToSubtitleGenerator->setMediaPod($mediaPod);

        $this->messageBus->dispatch($apiToSubtitleGenerator, [
            new AmqpStamp('api_to_subtitle_generator', 0, []),
        ]);
    }
}
