<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Service\ClerkTokenValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ClerkAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private ClerkTokenValidator $clerkTokenValidator,
        private UserRepository $userRepository,
        private string $authAdminKey,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('authorization');

        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided.');
        }

        if (str_replace('Bearer ', '', $apiToken) === $this->authAdminKey) {
            $user = $this->userRepository->findOneBy(['uuid' => $this->authAdminKey]);

            return new Passport(
                new UserBadge($user->getEmail(), function (string $userIdentifier): ?UserInterface {
                    return $this->userRepository->findOneBy(['uuid' => $this->authAdminKey]);
                }),
                new PasswordCredentials($user->getFirstName()),
            );
        }

        $result = preg_match('/Bearer\s(\S+)/', $apiToken, $matches);

        if (!$result) {
            throw new CustomUserMessageAuthenticationException('Token couldnt be decoded.');
        }

        $user = $this->clerkTokenValidator->validateToken($matches[1]);
        dd($user);

        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        return new Passport(
            new UserBadge($user->getClerkId(), function (string $userIdentifier): ?UserInterface {
                return $this->userRepository->findOneBy(['clerkId' => $userIdentifier]);
            }),
            new PasswordCredentials($user->getClerkId()),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => $exception->getMessage(),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
