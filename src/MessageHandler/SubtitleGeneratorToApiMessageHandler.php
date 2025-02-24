<?php

namespace App\MessageHandler;

use App\Protobuf\SubtitleGeneratorToApi;
use App\Service\MicroServicesHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SubtitleGeneratorToApiMessageHandler
{
    public function __construct(
        private MicroServicesHandler $microServicesHandler,
    ) {
    }

    public function __invoke(SubtitleGeneratorToApi $subtitleGeneratorToApi): void
    {
        $this->microServicesHandler->handle($subtitleGeneratorToApi->getMediaPod());
    }
}
