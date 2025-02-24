<?php

namespace App\MessageHandler;

use App\Protobuf\SubtitleIncrustatorToApi;
use App\Protobuf\VideoFormatterToApi;
use App\Service\MicroServicesHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class VideoFormatterToApiMessageHandler
{
    public function __construct(
        private MicroServicesHandler $microServicesHandler,
    ) {
    }

    public function __invoke(VideoFormatterToApi $videoFormatterToApi): void
    {
        $this->microServicesHandler->handle($videoFormatterToApi->getMediaPod());
    }
}
