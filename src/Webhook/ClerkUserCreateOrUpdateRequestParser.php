<?php

namespace App\Webhook;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;
use Symfony\Component\Webhook\Exception\RejectWebhookException;

final class ClerkUserCreateOrUpdateRequestParser extends AbstractRequestParser
{
    const EVENTS = ['user.created', 'user.updated'];
    
    public function __construct(
        private LoggerInterface $logger
    )
    {
    }
    
    protected function getRequestMatcher(): RequestMatcherInterface
    {
        return new ChainRequestMatcher([
            new MethodRequestMatcher('POST'),
            new IsJsonRequestMatcher(),
        ]);
    }

    /**
     * @throws JsonException
     */
    protected function doParse(Request $request, #[\SensitiveParameter] string $secret): ?RemoteEvent
    {
        $payload = $request->getPayload();
        
        $authToken = $request->headers->get('Secret');
        $type = $payload->getString('type');

        if ($authToken !== $secret) {
            throw new RejectWebhookException(Response::HTTP_UNAUTHORIZED, 'Invalid authentication token.');
        }

        if (!in_array($type, self::EVENTS)) {
            throw new RejectWebhookException(Response::HTTP_UNAUTHORIZED, 'This event is not authorized.');
        }

        if (!$request->getPayload()->has('data') || !$request->getPayload()->has('type') || !$request->getPayload()->has('instance_id')) {
            throw new RejectWebhookException(Response::HTTP_BAD_REQUEST, 'Request payload does not contain required fields.');
        }

        return new RemoteEvent(
            $payload->getString('type'),
            $payload->getString('instance_id'),
            $payload->all('data'),
        );
    }
}
