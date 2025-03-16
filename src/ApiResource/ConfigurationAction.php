<?php

namespace App\ApiResource;

use App\Dto\UploadVideoConfiguration;
use App\Entity\MediaPod;
use App\Entity\User;
use App\Repository\MediaPodRepository;
use App\Service\UploadVideoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ConfigurationAction extends AbstractController
{
    public function __construct(
        private MediaPodRepository $mediaPodRepository,
        private SerializerInterface $serializer,
    ) {
    }

    public function __invoke(#[CurrentUser()] ?User $user): JsonResponse 
    {
        if (null === $user) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $mediaPod = $user->getMediaPods()->first();

        if (!$mediaPod instanceof MediaPod) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['media-pods:get', 'default'])
            ->toArray();
       
        return new JsonResponse(data: $this->serializer->serialize($mediaPod->getConfiguration(), 'json', $context), status: Response::HTTP_OK, json: true);
    }
}
