<?php

namespace App\RemoteEvent;

use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('clerkUserCreateOrUpdate')]
final class ClerkUserCreateOrUpdateWebhookConsumer implements ConsumerInterface
{
    public function __construct(
        private UserRepository $userRepository
    )
    {
    }

    public function consume(RemoteEvent $event): void
    {
        $data = $event->getPayload();

        $this->userRepository->updateOrCreate([
            'clerkId' => $data['id'],
        ], [
            'clerkId' => $data['id'],
            'email' => $data['email_addresses'][0]['email_address'],
            'avatarUrl' => $data['profile_image_url'],
            'lastName' => $data['last_name'],
            'firstName' => $data['first_name'],
            'plainPassword' => $data['id'],
            'createdAt' => (new \DateTime())->setTimestamp($data['created_at'] / 100),
            'udpatedAt' => (new \DateTime())->setTimestamp($data['updated_at'] / 100),
        ]);
    }
}
