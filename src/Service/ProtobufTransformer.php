<?php

namespace App\Service;

use App\Entity\Configuration;
use App\Entity\MediaPod;
use App\Entity\Video;
use App\Protobuf\Configuration as ProtoConfiguration;
use App\Protobuf\MediaPod as ProtoMediaPod;
use App\Protobuf\Video as ProtoVideo;

class ProtobufTransformer
{
    public function transformProtobufToEntity(ProtoMediaPod $protobuf, ?MediaPod $mediaPod): MediaPod
    {
        if (!$mediaPod) {
            $mediaPod = new MediaPod();
        }

        if ($protobuf->getUuid()) {
            $mediaPod->setUuid($protobuf->getUuid());
        }

        if ($protobuf->getOriginalVideo()) {
            $mediaPod->setOriginalVideo(self::transformVideo($protobuf->getOriginalVideo(), $mediaPod->getOriginalVideo() ?? new Video()));
        }

        if ($protobuf->getProcessedVideo()) {
            $mediaPod->setProcessedVideo(self::transformVideo($protobuf->getProcessedVideo(), $mediaPod->getProcessedVideo() ?? new Video()));
        }

        if ($protobuf->getConfiguration()) {
            $mediaPod->setConfiguration(self::transformConfiguration($protobuf->getConfiguration(), $mediaPod->getConfiguration()));
        }

        return $mediaPod;
    }

    public function transformEntityToProtobuf(MediaPod $entity): ProtoMediaPod
    {
        $protobuf = new ProtoMediaPod();

        if ($entity->getUuid()) {
            $protobuf->setUuid($entity->getUuid());
        }

        $protobuf->setUserUuid($entity->getUser()->getUuid());

        if ($entity->getStatus()) {
            $protobuf->setStatus($entity->getStatus());
        }

        if ($entity->getOriginalVideo()) {
            $protobuf->setOriginalVideo(self::transformEntityToProtobufVideo($entity->getOriginalVideo()));
        }

        if ($entity->getOriginalVideo()) {
            $protobuf->setOriginalVideo(self::transformEntityToProtobufVideo($entity->getOriginalVideo()));
        }

        if ($entity->getProcessedVideo()) {
            $protobuf->setProcessedVideo(self::transformEntityToProtobufVideo($entity->getProcessedVideo()));
        }

        if ($entity->getConfiguration()) {
            $protobuf->setConfiguration(self::transformEntityToProtobufConfiguration($entity->getConfiguration()));
        }

        return $protobuf;
    }

    private function transformVideo(ProtoVideo $protobuf, Video $video): Video
    {
        if ($protobuf->getName()) {
            $video->setName($protobuf->getName());
        }

        if ($protobuf->getMimeType()) {
            $video->setMimeType($protobuf->getMimeType());
        }

        if ($protobuf->getSize()) {
            $video->setSize($protobuf->getSize());
        }

        if ($protobuf->getLength()) {
            $video->setLength($protobuf->getLength());
        }

        if ($protobuf->getSize()) {
            $video->setSubtitle($protobuf->getSubtitle());
        }

        if ($protobuf->getAss()) {
            $video->setAss($protobuf->getAss());
        }

        if ($protobuf->getSubtitles()) {
            $video->setSubtitles([]);
            foreach ($protobuf->getSubtitles()->getIterator() as $iterator) {
                $video->addSubtitles($iterator);
            }
        }

        if ($protobuf->getAudios()) {
            $video->setAudios([]);
            foreach ($protobuf->getAudios()->getIterator() as $iterator) {
                $video->addAudios($iterator);
            }
        }

        return $video;
    }

    private function transformConfiguration(ProtoConfiguration $protobuf, Configuration $configuration): Configuration
    {
        if ($protobuf->getFormat()) {
            $configuration->setFormat($protobuf->getFormat());
        }
        if ($protobuf->getSplit()) {
            $configuration->setSplit($protobuf->getSplit());
        }

        if ($protobuf->getSubtitleFont()) {
            $configuration->setSubtitleFont($protobuf->getSubtitleFont());
        }

        if ($protobuf->getSubtitleSize()) {
            $configuration->setSubtitleSize($protobuf->getSubtitleSize());
        }

        if ($protobuf->getSubtitleColor()) {
            $configuration->setSubtitleColor($protobuf->getSubtitleColor());
        }

        if ($protobuf->getSubtitleBold()) {
            $configuration->setSubtitleBold($protobuf->getSubtitleBold());
        }

        if ($protobuf->getSubtitleItalic()) {
            $configuration->setSubtitleItalic($protobuf->getSubtitleItalic());
        }

        if ($protobuf->getSubtitleUnderline()) {
            $configuration->setSubtitleUnderline($protobuf->getSubtitleUnderline());
        }

        if ($protobuf->getSubtitleOutlineColor()) {
            $configuration->setSubtitleOutlineColor($protobuf->getSubtitleOutlineColor());
        }

        if ($protobuf->getSubtitleOutlineThickness()) {
            $configuration->setSubtitleOutlineThickness($protobuf->getSubtitleOutlineThickness());
        }

        if ($protobuf->getSubtitleShadow()) {
            $configuration->setSubtitleShadow($protobuf->getSubtitleShadow());
        }

        if ($protobuf->getSubtitleShadowColor()) {
            $configuration->setSubtitleShadowColor($protobuf->getSubtitleShadowColor());
        }

        return $configuration;
    }

    private function transformEntityToProtobufVideo(Video $entity): ProtoVideo
    {
        $protobuf = new ProtoVideo();

        if ($entity->getName()) {
            $protobuf->setName($entity->getName());
        }

        if ($entity->getMimeType()) {
            $protobuf->setMimeType($entity->getMimeType());
        }

        if ($entity->getSize()) {
            $protobuf->setSize($entity->getSize());
        }

        if ($entity->getLength()) {
            $protobuf->setLength($entity->getLength());
        }

        if ($entity->getSubtitle()) {
            $protobuf->setSubtitle($entity->getSubtitle());
        }

        if ($entity->getAss()) {
            $protobuf->setAss($entity->getAss());
        }

        if ($entity->getSubtitles()) {
            $protobuf->setSubtitles($entity->getSubtitles());
        }

        if ($entity->getAudios()) {
            $protobuf->setAudios($entity->getAudios());
        }

        return $protobuf;
    }

    private function transformEntityToProtobufConfiguration(Configuration $entity): ProtoConfiguration
    {
        $protobuf = new ProtoConfiguration();

        if ($entity->getSubtitleFont()) {
            $protobuf->setSubtitleFont($entity->getSubtitleFont());
        }

        if ($entity->getFormat()) {
            $protobuf->setFormat($entity->getFormat());
        }

        if ($entity->getSplit()) {
            $protobuf->setSplit($entity->getSplit());
        }

        if ($entity->getSubtitleSize()) {
            $protobuf->setSubtitleSize($entity->getSubtitleSize());
        }

        if ($entity->getSubtitleColor()) {
            $protobuf->setSubtitleColor($entity->getSubtitleColor());
        }

        if ($entity->getSubtitleBold()) {
            $protobuf->setSubtitleBold($entity->getSubtitleBold());
        }

        if ($entity->getSubtitleItalic()) {
            $protobuf->setSubtitleItalic($entity->getSubtitleItalic());
        }

        if ($entity->getSubtitleUnderline()) {
            $protobuf->setSubtitleUnderline($entity->getSubtitleUnderline());
        }

        if ($entity->getSubtitleOutlineColor()) {
            $protobuf->setSubtitleOutlineColor($entity->getSubtitleOutlineColor());
        }

        if ($entity->getSubtitleOutlineThickness()) {
            $protobuf->setSubtitleOutlineThickness($entity->getSubtitleOutlineThickness());
        }

        if ($entity->getSubtitleShadow()) {
            $protobuf->setSubtitleShadow($entity->getSubtitleShadow());
        }

        if ($entity->getSubtitleShadowColor()) {
            $protobuf->setSubtitleShadowColor($entity->getSubtitleShadowColor());
        }

        return $protobuf;
    }
}
