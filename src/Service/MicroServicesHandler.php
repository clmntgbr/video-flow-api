<?php

namespace App\Service;

use App\Entity\MediaPod;
use App\Protobuf\MediaPod as ProtoMediaPod;
use App\Repository\MediaPodRepository;
use Psr\Log\LoggerInterface;

final class MicroServicesHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private MediaPodRepository $mediaPodRepository,
        private MediaPodOrchestrator $mediaPodOrchestrator,
        private ProtobufTransformer $protobufTransformer,
    ) {
    }

    public function handle(ProtoMediaPod $protoMediaPod): void
    {
        $this->logger->info('############################################################################################################################################');
        $this->logger->info(sprintf('Received mediaPod uuid : %s', $protoMediaPod->getUuid()));

        $mediaPod = $this->mediaPodRepository->findOneBy([
            'uuid' => $protoMediaPod->getUuid(),
        ]);

        if (!$mediaPod instanceof MediaPod) {
            return;
        }

        $mediaPod = $this->protobufTransformer->transformProtobufToEntity($protoMediaPod, $mediaPod);
        $status = $protoMediaPod->getStatus();
        
        $this->mediaPodRepository->update($mediaPod, [
            'statuses' => [$status],
            'status' => $status,
        ]);

        $this->mediaPodOrchestrator->dispatch($protoMediaPod, $mediaPod, $status);
    }
}
