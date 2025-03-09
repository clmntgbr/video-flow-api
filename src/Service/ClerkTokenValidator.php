<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ClerkTokenValidator
{
    public function __construct(
        private UserRepository $userRepository,
        private string $clerkPublishableKey,
        private string $frontUrl,
    ) {}

    public function validateToken(string $token): User
    {
        $decodedTokens = $this->decodeJwtToken($token);
        
        if (!isset($decodedTokens['header']['kid'])) {
            throw new UnauthorizedHttpException('Invalid token header');
        }

        if (!isset($decodedTokens['header']['alg']) || $decodedTokens['header']['alg'] !== 'RS256') {
            throw new UnauthorizedHttpException('Invalid token algorithm');
        }

        if (!isset($decodedTokens['data']['azp']) && $decodedTokens['data']['azp'] !== $this->frontUrl) {
            throw new UnauthorizedHttpException('Invalid AZP value');
        }

        $currentTimestamp = time();

        if (!isset($decodedTokens['data']['exp']) && $currentTimestamp > $decodedTokens['data']['exp']) {
            throw new UnauthorizedHttpException('Invalid exp value');
        }

        $user = $this->userRepository->findOneBy([
            'clerkId' => $decodedTokens['data']['id'],
        ]);

        return $user;
    }

    private function decodeJwtToken(string $token)
    {
        $jwtParts = explode('.', $token);

        $header = json_decode(base64_decode($jwtParts[0]), true);
        $data = json_decode(base64_decode($jwtParts[1]), true);

        return [
            'header' => $header,
            'data' => $data
        ];
    }
}
