<?php

namespace App\MessageHandler;

use App\Protobuf\VideoSplitterToApi;
use App\Service\MicroServicesHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class VideoSplitterToApiMessageHandler
{
    public function __construct(
        private MicroServicesHandler $microServicesHandler,
    ) {
    }

    public function __invoke(VideoSplitterToApi $videoSplitterToApi): void
    {
        $this->microServicesHandler->handle($videoSplitterToApi->getMediaPod());
    }
}
