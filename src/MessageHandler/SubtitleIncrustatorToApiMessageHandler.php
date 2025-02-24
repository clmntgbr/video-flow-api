<?php

namespace App\MessageHandler;

use App\Protobuf\SubtitleIncrustatorToApi;
use App\Service\MicroServicesHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SubtitleIncrustatorToApiMessageHandler
{
    public function __construct(
        private MicroServicesHandler $microServicesHandler,
    ) {
    }

    public function __invoke(SubtitleIncrustatorToApi $subtitleIncrustatorToApi): void
    {
        $this->microServicesHandler->handle($subtitleIncrustatorToApi->getMediaPod());
    }
}
