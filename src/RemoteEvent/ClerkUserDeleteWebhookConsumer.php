<?php

namespace App\RemoteEvent;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('clerkUserDelete')]
final class ClerkUserDeleteWebhookConsumer implements ConsumerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    )
    {
    }

    public function consume(RemoteEvent $event): void
    {
        $data = $event->getPayload();

        $user = $this->userRepository->findOneBy(['clerkId' => $data['id']]);

        if (!$user instanceof User) {
            return;
        }

        $this->em->remove($user);
        $this->em->flush();
    }
}
