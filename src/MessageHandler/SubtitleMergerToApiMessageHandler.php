<?php

namespace App\MessageHandler;

use App\Protobuf\SubtitleMergerToApi;
use App\Service\MicroServicesHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SubtitleMergerToApiMessageHandler
{
    public function __construct(
        private MicroServicesHandler $microServicesHandler,
    ) {
    }

    public function __invoke(SubtitleMergerToApi $subtitleMergerToApi): void
    {
        $this->microServicesHandler->handle($subtitleMergerToApi->getMediaPod());
    }
}
