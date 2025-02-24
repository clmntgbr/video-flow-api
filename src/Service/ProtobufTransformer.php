<?php

namespace App\Service;

use App\Entity\MediaPod;
use App\Entity\Preset;
use App\Entity\Video;
use App\Protobuf\MediaPod as ProtoMediaPod;
use App\Protobuf\Preset as ProtoPreset;
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

        if ($protobuf->getFormat()) {
            $mediaPod->setFormat($protobuf->getFormat());
        }

        if ($protobuf->getOriginalVideo()) {
            $mediaPod->setOriginalVideo(self::transformVideo($protobuf->getOriginalVideo(), $mediaPod->getOriginalVideo() ?? new Video()));
        }

        if ($protobuf->getProcessedVideo()) {
            $mediaPod->setProcessedVideo(self::transformVideo($protobuf->getProcessedVideo(), $mediaPod->getProcessedVideo() ?? new Video()));
        }

        if ($protobuf->getPreset()) {
            $mediaPod->setPreset(self::transformPreset($protobuf->getPreset(), $mediaPod->getPreset()));
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

        if ($entity->getFormat()) {
            $protobuf->setFormat($entity->getFormat());
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

        if ($entity->getPreset()) {
            $protobuf->setPreset(self::transformEntityToProtobufPreset($entity->getPreset()));
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

    private function transformPreset(ProtoPreset $protobuf, Preset $preset): Preset
    {
        if ($protobuf->getSubtitleFont()) {
            $preset->setSubtitleFont($protobuf->getSubtitleFont());
        }

        if ($protobuf->getSubtitleSize()) {
            $preset->setSubtitleSize($protobuf->getSubtitleSize());
        }

        if ($protobuf->getSubtitleColor()) {
            $preset->setSubtitleColor($protobuf->getSubtitleColor());
        }

        if ($protobuf->getSubtitleBold()) {
            $preset->setSubtitleBold($protobuf->getSubtitleBold());
        }

        if ($protobuf->getSubtitleItalic()) {
            $preset->setSubtitleItalic($protobuf->getSubtitleItalic());
        }

        if ($protobuf->getSubtitleUnderline()) {
            $preset->setSubtitleUnderline($protobuf->getSubtitleUnderline());
        }

        if ($protobuf->getSubtitleOutlineColor()) {
            $preset->setSubtitleOutlineColor($protobuf->getSubtitleOutlineColor());
        }

        if ($protobuf->getSubtitleOutlineThickness()) {
            $preset->setSubtitleOutlineThickness($protobuf->getSubtitleOutlineThickness());
        }

        if ($protobuf->getSubtitleShadow()) {
            $preset->setSubtitleShadow($protobuf->getSubtitleShadow());
        }

        if ($protobuf->getSubtitleShadowColor()) {
            $preset->setSubtitleShadowColor($protobuf->getSubtitleShadowColor());
        }

        return $preset;
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

    private function transformEntityToProtobufPreset(Preset $entity): ProtoPreset
    {
        $protobuf = new ProtoPreset();

        if ($entity->getSubtitleFont()) {
            $protobuf->setSubtitleFont($entity->getSubtitleFont());
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
