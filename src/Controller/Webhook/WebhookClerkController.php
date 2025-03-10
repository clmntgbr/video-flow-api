<?php

namespace App\Controller\Webhook;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/webhook', name: 'webhook_')]
class WebhookClerkController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private string $secretWebhookUser,
        private string $secretWebhookUserDeleted,
    ) {
    }

    #[Route('/clerk/user', name: 'clerk_user', methods: ['GET', 'POST'])]
    public function clerkUser(Request $request)
    {
        $secret = $request->headers->get('secret', null);
        $event = $request->getPayload()->get('type', null);

        if ($secret !== $this->secretWebhookUser) {
            return new JsonResponse(['message' => 'This secret is not valid.'], Response::HTTP_BAD_REQUEST);
        }

        if ('user.created' !== $event && 'user.updated' !== $event) {
            return new JsonResponse(['message' => 'This event is not valid.'], Response::HTTP_BAD_REQUEST);
        }

        $payload = json_decode($request->getContent(), true);

        $this->userRepository->updateOrCreate([
            'clerkId' => $payload['data']['id'],
        ], [
            'clerkId' => $payload['data']['id'],
            'email' => $payload['data']['email_addresses'][0]['email_address'],
            'avatarUrl' => $payload['data']['profile_image_url'],
            'lastName' => $payload['data']['last_name'],
            'firstName' => $payload['data']['first_name'],
            'plainPassword' => $payload['data']['id'],
            'createdAt' => (new \DateTime())->setTimestamp($payload['data']['created_at'] / 100),
            'udpatedAt' => (new \DateTime())->setTimestamp($payload['data']['updated_at'] / 100),
        ]);

        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[Route('/clerk/user/deleted', name: 'clerk_user_deleted', methods: ['GET', 'POST'])]
    public function clerkUserDeleted(Request $request)
    {
        $secret = $request->headers->get('secret', null);
        $event = $request->getPayload()->get('type', null);

        if ($secret !== $this->secretWebhookUserDeleted) {
            return new JsonResponse(['message' => 'This secret is not valid.'], Response::HTTP_BAD_REQUEST);
        }

        if ('user.deleted' !== $event) {
            return new JsonResponse(['message' => 'This event is not valid.'], Response::HTTP_BAD_REQUEST);
        }

        $payload = json_decode($request->getContent(), true);

        $user = $this->userRepository->findOneBy(['clerkId' => $payload['data']['id']]);

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'This user does not exist.'], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
