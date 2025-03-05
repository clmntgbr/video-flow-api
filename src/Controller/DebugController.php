<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Entity\MediaPod;
use App\Entity\User;
use App\Entity\Video;
use App\Repository\MediaPodRepository;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/debug', name: 'api_debug')]
class DebugController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MediaPodRepository $mediaPodRepository,
        private VideoRepository $videoRepository,
        private readonly SerializerInterface $serializer,
        private readonly FilesystemOperator $awsStorage,
        private string $transportDsn,
    ) {
    }

    #[Route('/{service}', name: 'service', methods: ['GET'])]
    public function debug(#[CurrentUser] ?User $user, string $service): JsonResponse
    {
        $channel = $this->rabbitMqConnection();

        $video = $this->getOriginalVideo('464f7205-9d37-41b2-bb78-c2f652d7fc33');
        $mediaPod = $this->getMediaPod($user, 'e363934c-837f-49fa-9f4a-55bb9afcfcff', $video);

        $this->sendToS3($user, $mediaPod);

        $message = match ($service) {
            'api_to_sound_extractor' => $this->toSoundExtractor(),
            'api_to_subtitle_generator' => $this->toSubtitleGenerator(),
            'api_to_subtitle_transformer' => $this->toSubtitleTransformer(),
            'api_to_subtitle_merger' => $this->toSubtitleMerger(),
            'api_to_video_transformer' => $this->toVideoFormatter(),
            'api_to_subtitle_incrustator' => $this->toSubtitleIncrustator($user, $mediaPod),
            'api_to_video_splitter' => $this->toVideoSplitter($user, $mediaPod),
            default => throw new \Exception('This service does not exist.'),
        };

        $channel->basic_publish($message, 'messages', $service);

        $this->em->persist($mediaPod);
        $this->em->flush();

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['media-pods:get', 'default'])
            ->toArray();

        return new JsonResponse(data: $this->serializer->serialize($mediaPod, 'json', $context), status: Response::HTTP_OK, json: true);
    }

    private function getMediaPod(?User $user, string $uuid, Video $video)
    {
        $mediaPod = $this->mediaPodRepository->findOneBy(['uuid' => $uuid]);

        if ($mediaPod instanceof MediaPod) {
            return $mediaPod;
        }

        $mediaPod = new MediaPod();
        $mediaPod->setConfiguration(new Configuration());
        $mediaPod->setOriginalVideo($video);
        $mediaPod->setUser($user);
        $mediaPod->setUuid($uuid);

        return $mediaPod;
    }

    private function getOriginalVideo(string $uuid): Video
    {
        $video = $this->videoRepository->findOneBy(['uuid' => $uuid]);

        if ($video instanceof Video) {
            return $video;
        }

        $video = new Video();
        $video->setUuid('464f7205-9d37-41b2-bb78-c2f652d7fc33');
        $video->setOriginalName('video.mp4');
        $video->setName('f27644432084872be07b716b6b32af76.mp4');
        $video->setMimeType('video/mp4');
        $video->setSize('71541180');

        return $video;
    }

    private function toSoundExtractor(): AMQPMessage
    {
        $messageData = json_encode([
            'task' => 'tasks.process_message',
            'args' => [$this->getJsonSoundExtractor()],
            'queue' => 'api_to_sound_extractor',
        ]);

        $message = new AMQPMessage($messageData,
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'headers' => [
                    'type' => 'App\Protobuf\ApiToSoundExtractor',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return $message;
    }

    private function toSubtitleGenerator(): AMQPMessage
    {
        $messageData = json_encode([
            'task' => 'tasks.process_message',
            'args' => [$this->getJsonSubtitleGenerator()],
            'queue' => 'api_to_subtitle_generator',
        ]);

        $message = new AMQPMessage($messageData,
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'headers' => [
                    'type' => 'App\Protobuf\ApiToSubtitleGenerator',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return $message;
    }

    private function toSubtitleMerger(): AMQPMessage
    {
        $messageData = json_encode([
            'task' => 'tasks.process_message',
            'args' => [$this->getJsonSubtitleMerger()],
            'queue' => 'api_to_subtitle_merger',
        ]);

        $message = new AMQPMessage($messageData,
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'headers' => [
                    'type' => 'App\Protobuf\ApiToSubtitleMerger',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return $message;
    }

    private function toSubtitleTransformer(): AMQPMessage
    {
        $messageData = json_encode([
            'task' => 'tasks.process_message',
            'args' => [$this->getJsonSubtitleTransformer()],
            'queue' => 'api_to_subtitle_transformer',
        ]);

        $message = new AMQPMessage($messageData,
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'headers' => [
                    'type' => 'App\Protobuf\ApiToSubtitleTransformer',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return $message;
    }

    private function toVideoFormatter(): AMQPMessage
    {
        $messageData = json_encode([
            'task' => 'tasks.process_message',
            'args' => [$this->getJsonVideoFormatter()],
            'queue' => 'api_to_video_transformer',
        ]);

        $message = new AMQPMessage($messageData,
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'headers' => [
                    'type' => 'App\Protobuf\ApiToVideoFormatter',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return $message;
    }

    private function toSubtitleIncrustator(User $user, MediaPod $mediaPod): AMQPMessage
    {
        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_processed.mp4');
        $stream = fopen('/app/public/debug/f27644432084872be07b716b6b32af76_processed_subtitle_incrustator.mp4', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $messageData = json_encode([
            'task' => 'tasks.process_message',
            'args' => [$this->getJsonSubtitleIncrustator()],
            'queue' => 'api_to_subtitle_incrustator',
        ]);

        $message = new AMQPMessage($messageData,
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'headers' => [
                    'type' => 'App\Protobuf\ApiToSubtitleIncrustator',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return $message;
    }

    private function toVideoSplitter(User $user, MediaPod $mediaPod): AMQPMessage
    {
        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_processed.mp4');
        $stream = fopen('/app/public/debug/f27644432084872be07b716b6b32af76_processed_video_splitter.mp4', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $messageData = json_encode([
            'task' => 'tasks.process_message',
            'args' => [$this->getJsonVideoSplitter()],
            'queue' => 'api_to_video_splitter',
        ]);

        $message = new AMQPMessage($messageData,
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'headers' => [
                    'type' => 'App\Protobuf\ApiToVideoSplitter',
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return $message;
    }

    private function sendToS3(User $user, MediaPod $mediaPod): void
    {
        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76.mp4');
        $stream = fopen('/app/public/debug/f27644432084872be07b716b6b32af76.mp4', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        // Audios

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_1.wav');
        $stream = fopen('/app/public/debug/audios/f27644432084872be07b716b6b32af76_1.wav', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_2.wav');
        $stream = fopen('/app/public/debug/audios/f27644432084872be07b716b6b32af76_2.wav', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_3.wav');
        $stream = fopen('/app/public/debug/audios/f27644432084872be07b716b6b32af76_3.wav', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_4.wav');
        $stream = fopen('/app/public/debug/audios/f27644432084872be07b716b6b32af76_4.wav', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/audios/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_5.wav');
        $stream = fopen('/app/public/debug/audios/f27644432084872be07b716b6b32af76_5.wav', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        // Subtitles

        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76.srt');
        $stream = fopen('/app/public/debug/f27644432084872be07b716b6b32af76.srt', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76.ass');
        $stream = fopen('/app/public/debug/f27644432084872be07b716b6b32af76.ass', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_1.srt');
        $stream = fopen('/app/public/debug/subtitles/f27644432084872be07b716b6b32af76_1.srt', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_2.srt');
        $stream = fopen('/app/public/debug/subtitles/f27644432084872be07b716b6b32af76_2.srt', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_3.srt');
        $stream = fopen('/app/public/debug/subtitles/f27644432084872be07b716b6b32af76_3.srt', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_4.srt');
        $stream = fopen('/app/public/debug/subtitles/f27644432084872be07b716b6b32af76_4.srt', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_5.srt');
        $stream = fopen('/app/public/debug/subtitles/f27644432084872be07b716b6b32af76_5.srt', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);

        $path = sprintf('%s/%s/subtitles/%s', $user->getUuid(), $mediaPod->getUuid(), 'f27644432084872be07b716b6b32af76_5.srt');
        $stream = fopen('/app/public/debug/subtitles/f27644432084872be07b716b6b32af76_5.srt', 'r');
        $this->awsStorage->writeStream($path, $stream, [
            'visibility' => 'public',
        ]);
    }

    private function rabbitMqConnection(): AMQPChannel
    {
        $parsedDsn = parse_url($this->transportDsn);
        $user = $parsedDsn['user'] ?? 'guest';
        $password = $parsedDsn['pass'] ?? 'guest';
        $host = $parsedDsn['host'] ?? 'localhost';
        $port = $parsedDsn['port'] ?? 5672;
        $vhost = ltrim($parsedDsn['path'] ?? '/', '/') ?: '/';

        $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
        $channel = $connection->channel();
        $channel->exchange_declare('messages', 'direct', false, true, false);

        return $channel;
    }

    private function getJsonSoundExtractor(): string
    {
        return '{"mediaPod":{"uuid":"e363934c-837f-49fa-9f4a-55bb9afcfcff","userUuid":"da59434f-602f-4d39-879c-eb0950812737","originalVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76.mp4","mimeType":"video/mp4","size":"71541180"},"status":"SOUND_EXTRACTOR_PENDING","configuration":{"subtitleFont":"ARIAL","subtitleSize":"16","subtitleColor":"#ffffff","subtitleBold":"1","subtitleItalic":"0","subtitleUnderline":"0","subtitleOutlineColor":"#000000","subtitleOutlineThickness":"1","subtitleShadow":"1","subtitleShadowColor":"#000000","format":"ZOOMED_916","split":"4"}}}';
    }

    private function getJsonSubtitleGenerator(): string
    {
        return '{"mediaPod":{"uuid":"e363934c-837f-49fa-9f4a-55bb9afcfcff","userUuid":"da59434f-602f-4d39-879c-eb0950812737","originalVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76.mp4","mimeType":"video/mp4","size":"71541180","length":"1449","audios":["f27644432084872be07b716b6b32af76_1.wav","f27644432084872be07b716b6b32af76_2.wav","f27644432084872be07b716b6b32af76_3.wav","f27644432084872be07b716b6b32af76_4.wav","f27644432084872be07b716b6b32af76_5.wav"]},"status":"SOUND_EXTRACTOR_COMPLETE","configuration":{"subtitleFont":"ARIAL","subtitleSize":"16","subtitleColor":"#ffffff","subtitleBold":"1","subtitleItalic":"0","subtitleUnderline":"0","subtitleOutlineColor":"#000000","subtitleOutlineThickness":"1","subtitleShadow":"1","subtitleShadowColor":"#000000","format":"ZOOMED_916","split":"4"}}}';
    }

    private function getJsonSubtitleMerger(): string
    {
        return '{"mediaPod":{"uuid":"e363934c-837f-49fa-9f4a-55bb9afcfcff","userUuid":"da59434f-602f-4d39-879c-eb0950812737","originalVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76.mp4","mimeType":"video/mp4","size":"71541180","length":"1449","subtitles":["f27644432084872be07b716b6b32af76_1.srt","f27644432084872be07b716b6b32af76_2.srt","f27644432084872be07b716b6b32af76_3.srt","f27644432084872be07b716b6b32af76_4.srt","f27644432084872be07b716b6b32af76_5.srt"],"audios":["f27644432084872be07b716b6b32af76_1.wav","f27644432084872be07b716b6b32af76_2.wav","f27644432084872be07b716b6b32af76_3.wav","f27644432084872be07b716b6b32af76_4.wav","f27644432084872be07b716b6b32af76_5.wav"]},"status":"SUBTITLE_GENERATOR_COMPLETE","configuration":{"subtitleFont":"ARIAL","subtitleSize":"16","subtitleColor":"#ffffff","subtitleBold":"1","subtitleItalic":"0","subtitleUnderline":"0","subtitleOutlineColor":"#000000","subtitleOutlineThickness":"1","subtitleShadow":"1","subtitleShadowColor":"#000000","format":"ZOOMED_916","split":"4"}}}';
    }

    private function getJsonSubtitleTransformer(): string
    {
        return '{"mediaPod":{"uuid":"e363934c-837f-49fa-9f4a-55bb9afcfcff","userUuid":"da59434f-602f-4d39-879c-eb0950812737","originalVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76.mp4","mimeType":"video/mp4","size":"71541180","length":"1449","subtitle":"f27644432084872be07b716b6b32af76.srt","subtitles":["f27644432084872be07b716b6b32af76_1.srt","f27644432084872be07b716b6b32af76_2.srt","f27644432084872be07b716b6b32af76_3.srt","f27644432084872be07b716b6b32af76_4.srt","f27644432084872be07b716b6b32af76_5.srt"],"audios":["f27644432084872be07b716b6b32af76_1.wav","f27644432084872be07b716b6b32af76_2.wav","f27644432084872be07b716b6b32af76_3.wav","f27644432084872be07b716b6b32af76_4.wav","f27644432084872be07b716b6b32af76_5.wav"]},"status":"SUBTITLE_MERGER_COMPLETE","configuration":{"subtitleFont":"ARIAL","subtitleSize":"16","subtitleColor":"#ffffff","subtitleBold":"1","subtitleItalic":"0","subtitleUnderline":"0","subtitleOutlineColor":"#000000","subtitleOutlineThickness":"1","subtitleShadow":"1","subtitleShadowColor":"#000000","format":"ZOOMED_916","split":"4"}}}';
    }

    private function getJsonVideoFormatter(): string
    {
        return '{"mediaPod":{"uuid":"e363934c-837f-49fa-9f4a-55bb9afcfcff","userUuid":"da59434f-602f-4d39-879c-eb0950812737","originalVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76.mp4","mimeType":"video/mp4","size":"71541180","length":"1449","subtitle":"f27644432084872be07b716b6b32af76.srt","ass":"f27644432084872be07b716b6b32af76.ass","subtitles":["f27644432084872be07b716b6b32af76_1.srt","f27644432084872be07b716b6b32af76_2.srt","f27644432084872be07b716b6b32af76_3.srt","f27644432084872be07b716b6b32af76_4.srt","f27644432084872be07b716b6b32af76_5.srt"],"audios":["f27644432084872be07b716b6b32af76_1.wav","f27644432084872be07b716b6b32af76_2.wav","f27644432084872be07b716b6b32af76_3.wav","f27644432084872be07b716b6b32af76_4.wav","f27644432084872be07b716b6b32af76_5.wav"]},"status":"SUBTITLE_TRANSFORMER_COMPLETE","configuration":{"subtitleFont":"ARIAL","subtitleSize":"16","subtitleColor":"#ffffff","subtitleBold":"1","subtitleItalic":"0","subtitleUnderline":"0","subtitleOutlineColor":"#000000","subtitleOutlineThickness":"1","subtitleShadow":"1","subtitleShadowColor":"#000000","format":"ZOOMED_916","split":"4"}}}';
    }

    private function getJsonSubtitleIncrustator(): string
    {
        return '{"mediaPod":{"uuid":"e363934c-837f-49fa-9f4a-55bb9afcfcff","userUuid":"da59434f-602f-4d39-879c-eb0950812737","originalVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76.mp4","mimeType":"video/mp4","size":"71541180","length":"1449","subtitle":"f27644432084872be07b716b6b32af76.srt","ass":"f27644432084872be07b716b6b32af76.ass","subtitles":["f27644432084872be07b716b6b32af76_1.srt","f27644432084872be07b716b6b32af76_2.srt","f27644432084872be07b716b6b32af76_3.srt","f27644432084872be07b716b6b32af76_4.srt","f27644432084872be07b716b6b32af76_5.srt"],"audios":["f27644432084872be07b716b6b32af76_1.wav","f27644432084872be07b716b6b32af76_2.wav","f27644432084872be07b716b6b32af76_3.wav","f27644432084872be07b716b6b32af76_4.wav","f27644432084872be07b716b6b32af76_5.wav"]},"status":"VIDEO_FORMATTER_COMPLETE","configuration":{"subtitleFont":"ARIAL","subtitleSize":"16","subtitleColor":"#ffffff","subtitleBold":"1","subtitleItalic":"0","subtitleUnderline":"0","subtitleOutlineColor":"#000000","subtitleOutlineThickness":"1","subtitleShadow":"1","subtitleShadowColor":"#000000","format":"ZOOMED_916","split":"4"},"processedVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76_processed.mp4","mimeType":"video/mp4","size":"71541180","length":"1449","subtitle":"f27644432084872be07b716b6b32af76.srt","ass":"f27644432084872be07b716b6b32af76.ass","subtitles":["f27644432084872be07b716b6b32af76_1.srt","f27644432084872be07b716b6b32af76_2.srt","f27644432084872be07b716b6b32af76_3.srt","f27644432084872be07b716b6b32af76_4.srt","f27644432084872be07b716b6b32af76_5.srt"],"audios":["f27644432084872be07b716b6b32af76_1.wav","f27644432084872be07b716b6b32af76_2.wav","f27644432084872be07b716b6b32af76_3.wav","f27644432084872be07b716b6b32af76_4.wav","f27644432084872be07b716b6b32af76_5.wav"]}}}';
    }

    private function getJsonVideoSplitter(): string
    {
        return '{"mediaPod":{"uuid":"e363934c-837f-49fa-9f4a-55bb9afcfcff","userUuid":"da59434f-602f-4d39-879c-eb0950812737","originalVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76.mp4","mimeType":"video/mp4","size":"71541180","length":"1449","subtitle":"f27644432084872be07b716b6b32af76.srt","ass":"f27644432084872be07b716b6b32af76.ass","subtitles":["f27644432084872be07b716b6b32af76_1.srt","f27644432084872be07b716b6b32af76_2.srt","f27644432084872be07b716b6b32af76_3.srt","f27644432084872be07b716b6b32af76_4.srt","f27644432084872be07b716b6b32af76_5.srt"],"audios":["f27644432084872be07b716b6b32af76_1.wav","f27644432084872be07b716b6b32af76_2.wav","f27644432084872be07b716b6b32af76_3.wav","f27644432084872be07b716b6b32af76_4.wav","f27644432084872be07b716b6b32af76_5.wav"]},"status":"SUBTITLE_INCRUSTATOR_COMPLETE","configuration":{"subtitleFont":"ARIAL","subtitleSize":"16","subtitleColor":"#ffffff","subtitleBold":"1","subtitleItalic":"0","subtitleUnderline":"0","subtitleOutlineColor":"#000000","subtitleOutlineThickness":"1","subtitleShadow":"1","subtitleShadowColor":"#000000","format":"ZOOMED_916","split":"4"},"processedVideo":{"uuid":"464f7205-9d37-41b2-bb78-c2f652d7fc33","name":"f27644432084872be07b716b6b32af76_processed.mp4","mimeType":"video/mp4","size":"71541180","length":"1449","subtitle":"f27644432084872be07b716b6b32af76.srt","ass":"f27644432084872be07b716b6b32af76.ass","subtitles":["f27644432084872be07b716b6b32af76_1.srt","f27644432084872be07b716b6b32af76_2.srt","f27644432084872be07b716b6b32af76_3.srt","f27644432084872be07b716b6b32af76_4.srt","f27644432084872be07b716b6b32af76_5.srt"],"audios":["f27644432084872be07b716b6b32af76_1.wav","f27644432084872be07b716b6b32af76_2.wav","f27644432084872be07b716b6b32af76_3.wav","f27644432084872be07b716b6b32af76_4.wav","f27644432084872be07b716b6b32af76_5.wav"]}}}';
    }
}
