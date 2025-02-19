<?php

namespace App\Controller;

use App\Entity\MediaPod;
use App\Entity\Preset;
use App\Entity\User;
use App\Entity\Video;
use App\Protobuf\MediaPod as ProtoMediaPod;
use App\Protobuf\MediaPodStatus;
use App\Protobuf\Preset as ProtoPreset;
use App\Protobuf\SubtitleGeneratorToApi;
use App\Protobuf\Video as ProtoVideo;
use App\Repository\MediaPodRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/debug', name: 'api_debug')]
class DebugController extends AbstractController
{
    #[Route('/subtitle_generator_api', name: 'subtitle_generator_api', methods: ['GET'])]
    public function subtitleGeneratorApi(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager, MediaPodRepository $mediaPodRepository, FilesystemOperator $awsStorage, MessageBusInterface $messageBus): JsonResponse
    {
        $mediaPodData = [
            '@id' => '/api/media_pods/35d74015-4b0e-46e9-a64c-044a75f27f15',
            '@type' => 'MediaPod',
            'videoName' => null,
            'originalVideo' => [
                '@id' => '/api/videos/7fb5d19e-002a-49d6-ba06-5f9f879137f7',
                '@type' => 'Video',
                'originalName' => 'video5.mp4',
                'name' => '136cc2c2a2923f41987c67ca9845f9ff.mp4',
                'mimeType' => 'video/mp4',
                'size' => 71541180,
                'subtitles' => [],
                'audios' => [
                    '136cc2c2a2923f41987c67ca9845f9ff_1.wav',
                    '136cc2c2a2923f41987c67ca9845f9ff_2.wav',
                    '136cc2c2a2923f41987c67ca9845f9ff_3.wav',
                    '136cc2c2a2923f41987c67ca9845f9ff_4.wav',
                    '136cc2c2a2923f41987c67ca9845f9ff_5.wav',
                ],
                'createdAt' => '2025-02-08T21:08:34+00:00',
                'updatedAt' => '2025-02-08T21:08:54+00:00',
                'uuid' => '7fb5d19e-002a-49d6-ba06-5f9f879137f7',
            ],
            'preset' => [
                'subtitleFont' => 'ARIAL',
                'subtitleSize' => '18',
                'subtitleColor' => '#008000',
                'subtitleBackground' => '#FF0000',
                'subtitleOutlineColor' => '#FFFF00',
                'subtitleOutlineThickness' => '3',
                'subtitleShadow' => 'NONE',
                'subtitleShadowColor' => '#0000FF',
            ],
            'status' => 'subtitle_generator_pending',
            'statuses' => [
                MediaPodStatus::name(MediaPodStatus::UPLOAD_COMPLETE),
                MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_PENDING),
                MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_COMPLETE),
                MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_PENDING),
            ],
            'createdAt' => '2025-02-08T21:08:34+00:00',
            'updatedAt' => '2025-02-08T21:08:54+00:00',
            'uuid' => '35d74015-4b0e-46e9-a64c-044a75f27f15',
        ];

        $mediaPod = $mediaPodRepository->findOneBy(['uuid' => '35d74015-4b0e-46e9-a64c-044a75f27f15']);

        if (!$mediaPod instanceof MediaPod) {
            $video = new Video();
            $video->setOriginalName($mediaPodData['originalVideo']['originalName']);
            $video->setUuid($mediaPodData['originalVideo']['uuid']);
            $video->setName($mediaPodData['originalVideo']['name']);
            $video->setMimeType($mediaPodData['originalVideo']['mimeType']);
            $video->setSize($mediaPodData['originalVideo']['size']);
            $video->setSubtitles($mediaPodData['originalVideo']['subtitles']);
            $video->setAudios($mediaPodData['originalVideo']['audios']);
            $video->setCreatedAt(new \DateTime($mediaPodData['originalVideo']['createdAt']));
            $video->setUpdatedAt(new \DateTime($mediaPodData['originalVideo']['updatedAt']));

            $preset = new Preset();
            $preset->setSubtitleFont($mediaPodData['preset']['subtitleFont']);
            $preset->setSubtitleSize($mediaPodData['preset']['subtitleSize']);
            $preset->setSubtitleColor($mediaPodData['preset']['subtitleColor']);
            $preset->setSubtitleBackground($mediaPodData['preset']['subtitleBackground']);
            $preset->setSubtitleOutlineColor($mediaPodData['preset']['subtitleOutlineColor']);
            $preset->setSubtitleOutlineThickness($mediaPodData['preset']['subtitleOutlineThickness']);
            $preset->setSubtitleShadow($mediaPodData['preset']['subtitleShadow']);
            $preset->setSubtitleShadowColor($mediaPodData['preset']['subtitleShadowColor']);

            $mediaPod = new MediaPod();
            $mediaPod->setUser($user);
            $mediaPod->setUuid($mediaPodData['uuid']);
            $mediaPod->setVideoName($mediaPodData['videoName']);
            $mediaPod->setOriginalVideo($video);
            $mediaPod->setPreset($preset);
            $mediaPod->setStatus($mediaPodData['status']);
            $mediaPod->setStatuses($mediaPodData['statuses']);
            $mediaPod->setCreatedAt(new \DateTime($mediaPodData['createdAt']));
            $mediaPod->setUpdatedAt(new \DateTime($mediaPodData['updatedAt']));

            $entityManager->persist($mediaPod);
            $entityManager->flush();
        }

        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff.mp4');
        $stream = fopen('/app/public/debug/136cc2c2a2923f41987c67ca9845f9ff.mp4', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        // Audios

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_1.wav');
        $stream = fopen('/app/public/debug/audios/136cc2c2a2923f41987c67ca9845f9ff_1.wav', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_2.wav');
        $stream = fopen('/app/public/debug/audios/136cc2c2a2923f41987c67ca9845f9ff_2.wav', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_3.wav');
        $stream = fopen('/app/public/debug/audios/136cc2c2a2923f41987c67ca9845f9ff_3.wav', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_4.wav');
        $stream = fopen('/app/public/debug/audios/136cc2c2a2923f41987c67ca9845f9ff_4.wav', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_5.wav');
        $stream = fopen('/app/public/debug/audios/136cc2c2a2923f41987c67ca9845f9ff_5.wav', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        // Subtitles

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_1.srt');
        $stream = fopen('/app/public/debug/subtitles/136cc2c2a2923f41987c67ca9845f9ff_1.srt', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_2.srt');
        $stream = fopen('/app/public/debug/subtitles/136cc2c2a2923f41987c67ca9845f9ff_2.srt', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_3.srt');
        $stream = fopen('/app/public/debug/subtitles/136cc2c2a2923f41987c67ca9845f9ff_3.srt', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_4.srt');
        $stream = fopen('/app/public/debug/subtitles/136cc2c2a2923f41987c67ca9845f9ff_4.srt', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_5.srt');
        $stream = fopen('/app/public/debug/subtitles/136cc2c2a2923f41987c67ca9845f9ff_5.srt', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $protoVideo = new ProtoVideo();
        $protoVideo->setName($mediaPodData['originalVideo']['name']);
        $protoVideo->setMimeType($mediaPodData['originalVideo']['mimeType']);
        $protoVideo->setSize($mediaPodData['originalVideo']['size']);
        $protoVideo->setAudios($mediaPodData['originalVideo']['audios']);
        $protoVideo->setSubtitles([
            '136cc2c2a2923f41987c67ca9845f9ff_1.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_2.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_3.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_4.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_5.srt',
        ]);

        $protoPreset = new ProtoPreset();
        $protoPreset->setSubtitleFont($mediaPodData['preset']['subtitleFont']);
        $protoPreset->setSubtitleSize($mediaPodData['preset']['subtitleSize']);
        $protoPreset->setSubtitleColor($mediaPodData['preset']['subtitleColor']);
        $protoPreset->setSubtitleBackground($mediaPodData['preset']['subtitleBackground']);
        $protoPreset->setSubtitleOutlineColor($mediaPodData['preset']['subtitleOutlineColor']);
        $protoPreset->setSubtitleOutlineThickness($mediaPodData['preset']['subtitleOutlineThickness']);
        $protoPreset->setSubtitleShadow($mediaPodData['preset']['subtitleShadow']);
        $protoPreset->setSubtitleShadowColor($mediaPodData['preset']['subtitleShadowColor']);

        $protoMediaPod = new ProtoMediaPod();
        $protoMediaPod->setUuid($mediaPodData['uuid']);
        $protoMediaPod->setUserUuid($user->getUuid());
        $protoMediaPod->setOriginalVideo($protoVideo);
        $protoMediaPod->setPreset($protoPreset);
        $protoMediaPod->setStatus(MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_COMPLETE));

        $subtitleGeneratorApi = new SubtitleGeneratorToApi();
        $subtitleGeneratorApi->setMediaPod($protoMediaPod);

        $messageBus->dispatch($subtitleGeneratorApi, [
            new AmqpStamp('subtitle_generator_to_api', 0, []),
        ]);

        return new JsonResponse(data: [], status: Response::HTTP_CREATED);
    }
}
