<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class JwtResponseListener
{
    public function __construct(
        private readonly NormalizerInterface $serializer,
    ) {
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['user:get', 'media-pods:get', 'default'])
            ->toArray();

        $data['user'] = $this->serializer->normalize($user, null, $context);
        $event->setData($data);
    }
}
