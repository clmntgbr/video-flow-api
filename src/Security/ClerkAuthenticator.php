<?php

namespace App\Security;

use App\Entity\User;
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
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ClerkAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private ClerkTokenValidator $clerkTokenValidator,
        private UserRepository $userRepository,
        private string $masterKey,
    )
    {
        
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

        if (str_replace('Bearer ', '', $apiToken) === $this->masterKey) {
            $user = $this->userRepository->findOneBy(['uuid' => $this->masterKey]);
            
            return new Passport(
                new UserBadge($user->getEmail(), function (string $userIdentifier): ?UserInterface {
                    return $this->userRepository->findOneBy(['uuid' => $this->masterKey]);
                }),
                new PasswordCredentials($user->getFirstName()),
            );
        }

        $result = preg_match('/Bearer\s(\S+)/', $apiToken, $matches);

        if (!$result) {
            throw new CustomUserMessageAuthenticationException('Token couldnt be decoded.');
        }

        $user = $this->clerkTokenValidator->validateToken($matches[1]);

        return new Passport(
            new UserBadge($user->getEmail(), function (string $userIdentifier): ?UserInterface {
                return $this->userRepository->findOneBy(['email' => $userIdentifier]);
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
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
