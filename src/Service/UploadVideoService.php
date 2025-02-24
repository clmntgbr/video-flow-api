<?php

namespace App\Service;

use App\Dto\UploadVideoPreset;
use App\Entity\MediaPod;
use App\Entity\User;
use App\Protobuf\MediaPodStatus;
use App\Repository\MediaPodRepository;
use App\Repository\PresetRepository;
use App\Repository\VideoRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadVideoService
{
    public function __construct(
        private ValidatorInterface $validator,
        private FilesystemOperator $awsStorage,
        private Security $security,
        private MediaPodRepository $mediaPodRepository,
        private VideoRepository $videoRepository,
        private PresetRepository $presetRepository,
        private ProtobufService $protobufService,
    ) {
    }

    public function upload(UploadedFile $file, UploadVideoPreset $uploadVideoPreset): JsonResponse
    {
        $constraints = new File([
            'mimeTypes' => [
                'video/mp4',
            ],
            'mimeTypesMessage' => 'Please upload a valid video file (MP4).',
        ]);

        $violations = $this->validator->validate($file, $constraints);

        if (count($violations) > 0) {
            return new JsonResponse([
                'message' => $violations[0]->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'message' => 'You must be logged in to upload a video.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $mediaPodUuid = Uuid::v4()->toString();
            $fileName = sprintf('%s.%s', md5(uniqid()), $file->guessExtension());
            $path = sprintf('%s/%s/%s', $user->getUuid(), $mediaPodUuid, $fileName);

            $stream = fopen($file->getPathname(), 'r');

            if (false === $stream) {
                return new JsonResponse([
                    'message' => 'An error occurred during the upload.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $this->awsStorage->writeStream($path, $stream, [
                'visibility' => 'public',
                'mimetype' => $file->getMimeType(),
            ]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            $mediaPod = $this->createMediaPod($file, $uploadVideoPreset, $fileName, $mediaPodUuid);
            $this->protobufService->toSoundExtractor($file, $mediaPod, $user, $fileName);

            return new JsonResponse([
                'message' => 'Video uploaded successfully.',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            dd($e);

            return new JsonResponse([
                'message' => 'An error occurred during the upload: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createMediaPod(UploadedFile $uploadedFile, UploadVideoPreset $uploadVideoPreset, string $fileName, string $mediaPodUuid): MediaPod
    {
        $video = $this->videoRepository->create([
            'mimeType' => $uploadedFile->getMimeType(),
            'originalName' => $uploadedFile->getClientOriginalName(),
            'name' => $fileName,
            'size' => $uploadedFile->getSize(),
        ]);

        $preset = $this->presetRepository->create([
            'subtitleFont' => $uploadVideoPreset->subtitleFont,
            'subtitleSize' => $uploadVideoPreset->subtitleSize,
            'subtitleColor' => $uploadVideoPreset->subtitleColor,
            'subtitleBold' => $uploadVideoPreset->subtitleBold,
            'subtitleItalic' => $uploadVideoPreset->subtitleItalic,
            'subtitleUnderline' => $uploadVideoPreset->subtitleUnderline,
            'subtitleOutlineColor' => $uploadVideoPreset->subtitleOutlineColor,
            'subtitleOutlineThickness' => $uploadVideoPreset->subtitleOutlineThickness,
            'subtitleShadow' => $uploadVideoPreset->subtitleShadow,
            'subtitleShadowColor' => $uploadVideoPreset->subtitleShadowColor,
        ]);

        $mediaPod = $this->mediaPodRepository->create([
            'user' => $this->security->getUser(),
            'uuid' => $mediaPodUuid,
            'originalVideo' => $video,
            'preset' => $preset,
            'status' => MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_PENDING),
            'statuses' => [
                MediaPodStatus::name(MediaPodStatus::UPLOAD_COMPLETE),
                MediaPodStatus::name(MediaPodStatus::SOUND_EXTRACTOR_PENDING),
            ],
        ]);

        return $mediaPod;
    }
}
