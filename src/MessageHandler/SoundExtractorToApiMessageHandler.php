<?php

namespace App\MessageHandler;

use App\Protobuf\SoundExtractorToApi;
use App\Service\MicroServicesHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SoundExtractorToApiMessageHandler
{
    public function __construct(
        private MicroServicesHandler $microServicesHandler,
    ) {
    }

    public function __invoke(SoundExtractorToApi $soundExtractorToApi): void
    {
        $this->microServicesHandler->handle($soundExtractorToApi->getMediaPod());
    }
}
