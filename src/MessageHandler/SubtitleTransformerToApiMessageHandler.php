<?php

namespace App\MessageHandler;

use App\Protobuf\SubtitleTransformerToApi;
use App\Service\MicroServicesHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SubtitleTransformerToApiMessageHandler
{
    public function __construct(
        private MicroServicesHandler $microServicesHandler,
    ) {
    }

    public function __invoke(SubtitleTransformerToApi $subtitleTransformerToApi): void
    {
        $this->microServicesHandler->handle($subtitleTransformerToApi->getMediaPod());
    }
}
