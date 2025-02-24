<?php

namespace App\Controller;

use App\Entity\MediaPod;
use App\Entity\Preset;
use App\Entity\User;
use App\Entity\Video;
use App\Protobuf\ApiToVideoFormatter;
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
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    private function getMediaPodData()
    {
        return [
            '@id' => '/api/media_pods/35d74015-4b0e-46e9-a64c-044a75f27f15',
            '@type' => 'MediaPod',
            'videoName' => null,
            'format' => 'ORIGINAL',
            'originalVideo' => [
                '@id' => '/api/videos/7fb5d19e-002a-49d6-ba06-5f9f879137f7',
                '@type' => 'Video',
                'originalName' => 'video5.mp4',
                'name' => '136cc2c2a2923f41987c67ca9845f9ff.mp4',
                'mimeType' => 'video/mp4',
                'size' => 71541180,
                'length' => '240',
                'subtitle' => '136cc2c2a2923f41987c67ca9845f9ff.srt',
                'ass' => '136cc2c2a2923f41987c67ca9845f9ff.ass',
                'subtitles' => [
                    '136cc2c2a2923f41987c67ca9845f9ff_1.srt',
                    '136cc2c2a2923f41987c67ca9845f9ff_2.srt',
                    '136cc2c2a2923f41987c67ca9845f9ff_3.srt',
                    '136cc2c2a2923f41987c67ca9845f9ff_4.srt',
                    '136cc2c2a2923f41987c67ca9845f9ff_5.srt',
                ],
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
            'processedVideo' => [
                '@id' => '/api/videos/7fb5d19e-002a-49d6-ba06-5f9f879137f7',
                '@type' => 'Video',
                'originalName' => 'video5.mp4',
                'name' => '136cc2c2a2923f41987c67ca9845f9ff_processed.mp4',
                'mimeType' => 'video/mp4',
                'size' => 71541180,
                'length' => '240',
                'createdAt' => '2025-02-08T21:08:34+00:00',
                'updatedAt' => '2025-02-08T21:08:54+00:00',
                'uuid' => '7fb5d19e-002a-49d6-ba06-5f9f879137f7',
            ],
            'preset' => [
                'subtitleFont' => 'ARIAL',
                'subtitleSize' => '20',
                'subtitleColor' => '#FFFFFF',
                'subtitleBold' => '0',
                'subtitleItalic' => '0',
                'subtitleUnderline' => '0',
                'subtitleOutlineColor' => '#000000',
                'subtitleOutlineThickness' => '2',
                'subtitleShadow' => '2',
                'subtitleShadowColor' => '#000000',
            ],
            'status' => 'subtitle_generator_pending',
            'statuses' => [
                MediaPodStatus::name(MediaPodStatus::UPLOAD_COMPLETE),
                MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_PENDING),
                MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_COMPLETE),
                MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_PENDING),
            ],
            'createdAt' => '2025-02-08T21:08:34+00:00',
            'updatedAt' => '2025-02-08T21:08:54+00:00',
            'uuid' => '35d74015-4b0e-46e9-a64c-044a75f27f15',
        ];
    }

    private function createMediaPodEntity(User $user, array $mediaPodData, Video $video): MediaPod
    {
        $mediaPod = new MediaPod();
        $mediaPod->setUser($user);
        $mediaPod->setFormat($mediaPodData['format']);
        $mediaPod->setUuid($mediaPodData['uuid']);
        $mediaPod->setVideoName($mediaPodData['videoName']);
        $mediaPod->setOriginalVideo($video);
        $mediaPod->setPreset(new Preset());
        $mediaPod->setStatus($mediaPodData['status']);
        $mediaPod->setStatuses($mediaPodData['statuses']);
        $mediaPod->setCreatedAt(new \DateTime($mediaPodData['createdAt']));
        $mediaPod->setUpdatedAt(new \DateTime($mediaPodData['updatedAt']));

        $this->em->persist($mediaPod);

        return $mediaPod;
    }

    private function createVideoEntity(array $mediaPodData): Video
    {
        $video = new Video();
        $video->setOriginalName($mediaPodData['originalVideo']['originalName']);
        $video->setUuid($mediaPodData['originalVideo']['uuid']);
        $video->setName($mediaPodData['originalVideo']['name']);
        $video->setMimeType($mediaPodData['originalVideo']['mimeType']);
        $video->setSize($mediaPodData['originalVideo']['size']);
        $video->setLength($mediaPodData['originalVideo']['length']);
        $video->setSubtitles($mediaPodData['originalVideo']['subtitles']);
        $video->setAudios($mediaPodData['originalVideo']['audios']);
        $video->setCreatedAt(new \DateTime($mediaPodData['originalVideo']['createdAt']));
        $video->setUpdatedAt(new \DateTime($mediaPodData['originalVideo']['updatedAt']));

        return $video;
    }

    private function sendToS3(User $user, MediaPod $mediaPod, FilesystemOperator $awsStorage): void
    {
        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff.mp4');
        $stream = fopen('/app/public/debug/136cc2c2a2923f41987c67ca9845f9ff.mp4', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff_processed.mp4');
        $stream = fopen('/app/public/debug/136cc2c2a2923f41987c67ca9845f9ff_processed.mp4', 'r');
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

        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff.srt');
        $stream = fopen('/app/public/debug/136cc2c2a2923f41987c67ca9845f9ff.srt', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), '136cc2c2a2923f41987c67ca9845f9ff.ass');
        $stream = fopen('/app/public/debug/136cc2c2a2923f41987c67ca9845f9ff.ass', 'r');
        $awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

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
    }

    #[Route('/subtitle_generator_api', name: 'subtitle_generator_api', methods: ['GET'])]
    public function subtitleGeneratorApi(#[CurrentUser] ?User $user, MediaPodRepository $mediaPodRepository, FilesystemOperator $awsStorage, MessageBusInterface $messageBus): JsonResponse
    {
        $mediaPodData = $this->getMediaPodData();

        $mediaPod = $mediaPodRepository->findOneBy(['uuid' => '35d74015-4b0e-46e9-a64c-044a75f27f15']);

        if (!$mediaPod instanceof MediaPod) {
            $video = $this->createVideoEntity($mediaPodData);
            $mediaPod = $this->createMediaPodEntity($user, $mediaPodData, $video);
            $this->em->flush();
        }

        $this->sendToS3($user, $mediaPod, $awsStorage);

        $protoVideo = new ProtoVideo();
        $protoVideo->setName($mediaPodData['originalVideo']['name']);
        $protoVideo->setMimeType($mediaPodData['originalVideo']['mimeType']);
        $protoVideo->setSize($mediaPodData['originalVideo']['size']);
        $protoVideo->setLength($mediaPodData['originalVideo']['length']);
        $protoVideo->setAudios($mediaPodData['originalVideo']['audios']);
        $protoVideo->setSubtitles([
            '136cc2c2a2923f41987c67ca9845f9ff_1.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_2.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_3.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_4.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_5.srt',
        ]);

        $protoPreset = new ProtoPreset();
        $protoPreset->setSubtitleFont($mediaPod->getPreset()->getSubtitleFont());
        $protoPreset->setSubtitleSize($mediaPod->getPreset()->getSubtitleSize());
        $protoPreset->setSubtitleColor($mediaPod->getPreset()->getSubtitleColor());
        $protoPreset->setSubtitleBold($mediaPod->getPreset()->getSubtitleBold());
        $protoPreset->setSubtitleItalic($mediaPod->getPreset()->getSubtitleItalic());
        $protoPreset->setSubtitleUnderline($mediaPod->getPreset()->getSubtitleUnderline());
        $protoPreset->setSubtitleOutlineColor($mediaPod->getPreset()->getSubtitleOutlineColor());
        $protoPreset->setSubtitleOutlineThickness($mediaPod->getPreset()->getSubtitleOutlineThickness());
        $protoPreset->setSubtitleShadow($mediaPod->getPreset()->getSubtitleShadow());
        $protoPreset->setSubtitleShadowColor($mediaPod->getPreset()->getSubtitleShadowColor());

        $protoMediaPod = new ProtoMediaPod();
        $protoMediaPod->setUuid($mediaPodData['uuid']);
        $protoMediaPod->setUserUuid($user->getUuid());
        $protoMediaPod->setOriginalVideo($protoVideo);
        $protoMediaPod->setFormat($mediaPodData['format']);
        $protoMediaPod->setPreset($protoPreset);
        $protoMediaPod->setStatus(MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_COMPLETE));

        $subtitleGeneratorApi = new SubtitleGeneratorToApi();
        $subtitleGeneratorApi->setMediaPod($protoMediaPod);

        $messageBus->dispatch($subtitleGeneratorApi, [
            new AmqpStamp('subtitle_generator_to_api', 0, []),
        ]);

        return new JsonResponse(data: [], status: Response::HTTP_CREATED);
    }

    #[Route('/subtitle_incrustator_api', name: 'subtitle_incrustator_api', methods: ['GET'])]
    public function subtitleIncrustatorApi(#[CurrentUser] ?User $user, MediaPodRepository $mediaPodRepository, FilesystemOperator $awsStorage, MessageBusInterface $messageBus): JsonResponse
    {
        $mediaPodData = $this->getMediaPodData();

        $mediaPod = $mediaPodRepository->findOneBy(['uuid' => '35d74015-4b0e-46e9-a64c-044a75f27f15']);

        if (!$mediaPod instanceof MediaPod) {
            $video = $this->createVideoEntity($mediaPodData);
            $mediaPod = $this->createMediaPodEntity($user, $mediaPodData, $video);
            $mediaPodRepository->update($mediaPod, [
                'status' => MediaPodStatus::name(MediaPodStatus::SUBTITLE_INCRUSTATOR_COMPLETE),
                'statuses' => [
                    MediaPodStatus::name(MediaPodStatus::SUBTITLE_GENERATOR_COMPLETE),
                    MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_PENDING),
                    MediaPodStatus::name(MediaPodStatus::SUBTITLE_MERGER_COMPLETE),
                    MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_PENDING),
                    MediaPodStatus::name(MediaPodStatus::SUBTITLE_TRANSFORMER_COMPLETE),
                    MediaPodStatus::name(MediaPodStatus::SUBTITLE_INCRUSTATOR_PENDING),
                    MediaPodStatus::name(MediaPodStatus::SUBTITLE_INCRUSTATOR_COMPLETE),
                    MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_PENDING),
                ],
            ]);
        }

        $this->sendToS3($user, $mediaPod, $awsStorage);

        $protoVideo = new ProtoVideo();
        $protoVideo->setName($mediaPodData['originalVideo']['name']);
        $protoVideo->setMimeType($mediaPodData['originalVideo']['mimeType']);
        $protoVideo->setSize($mediaPodData['originalVideo']['size']);
        $protoVideo->setLength($mediaPodData['originalVideo']['length']);
        $protoVideo->setSubtitle($mediaPodData['originalVideo']['subtitle']);
        $protoVideo->setAss($mediaPodData['originalVideo']['ass']);
        $protoVideo->setAudios($mediaPodData['originalVideo']['audios']);
        $protoVideo->setSubtitles([
            '136cc2c2a2923f41987c67ca9845f9ff_1.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_2.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_3.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_4.srt',
            '136cc2c2a2923f41987c67ca9845f9ff_5.srt',
        ]);

        $protoProcessedVideo = new ProtoVideo();
        $protoProcessedVideo->setName($mediaPodData['processedVideo']['name']);
        $protoProcessedVideo->setMimeType($mediaPodData['processedVideo']['mimeType']);
        $protoProcessedVideo->setSize($mediaPodData['processedVideo']['size']);
        $protoProcessedVideo->setLength($mediaPodData['processedVideo']['length']);

        $protoPreset = new ProtoPreset();
        $protoPreset->setSubtitleFont($mediaPod->getPreset()->getSubtitleFont());
        $protoPreset->setSubtitleSize($mediaPod->getPreset()->getSubtitleSize());
        $protoPreset->setSubtitleColor($mediaPod->getPreset()->getSubtitleColor());
        $protoPreset->setSubtitleBold($mediaPod->getPreset()->getSubtitleBold());
        $protoPreset->setSubtitleItalic($mediaPod->getPreset()->getSubtitleItalic());
        $protoPreset->setSubtitleUnderline($mediaPod->getPreset()->getSubtitleUnderline());
        $protoPreset->setSubtitleOutlineColor($mediaPod->getPreset()->getSubtitleOutlineColor());
        $protoPreset->setSubtitleOutlineThickness($mediaPod->getPreset()->getSubtitleOutlineThickness());
        $protoPreset->setSubtitleShadow($mediaPod->getPreset()->getSubtitleShadow());
        $protoPreset->setSubtitleShadowColor($mediaPod->getPreset()->getSubtitleShadowColor());

        $protoMediaPod = new ProtoMediaPod();
        $protoMediaPod->setUuid($mediaPodData['uuid']);
        $protoMediaPod->setUserUuid($user->getUuid());
        $protoMediaPod->setFormat($mediaPodData['format']);
        $protoMediaPod->setOriginalVideo($protoVideo);
        $protoMediaPod->setProcessedVideo($protoProcessedVideo);
        $protoMediaPod->setPreset($protoPreset);
        $protoMediaPod->setStatus(MediaPodStatus::name(MediaPodStatus::VIDEO_FORMATTER_PENDING));

        $apiToVideoFormatter = new ApiToVideoFormatter();
        $apiToVideoFormatter->setMediaPod($protoMediaPod);

        $messageBus->dispatch($apiToVideoFormatter, [
            new AmqpStamp('api_to_video_formatter', 0, []),
        ]);

        return new JsonResponse(data: [], status: Response::HTTP_CREATED);
    }
}
