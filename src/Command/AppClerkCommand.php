<?php

namespace App\Command;

use App\Entity\MediaPod;
use App\Entity\Tag;
use App\Repository\MediaPodRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clerk',
)]
class AppClerkCommand extends Command
{
    public function __construct(
        private MediaPodRepository $mediaPodRepository,
        private TagRepository $tagRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userRepository->findOneBy(['email' => 'c.goubier@nanotera.eu']);

        if (!$user) {
            return Command::SUCCESS;
        }

        $tags = $this->tagRepository->findAll();
        $mediaPods = $this->mediaPodRepository->findAll();

        /** @var MediaPod $mediaPod */
        foreach ($mediaPods as $mediaPod) {
            if (array_rand([0, 1]) !== 1) {
                continue;
            }

            $mediaPod->setUser($user);
            $this->em->persist($mediaPod);
            $this->em->flush();
        }

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $tag->setUser($user);
            $this->em->persist($tag);
            $this->em->flush();
        }
        
        return Command::SUCCESS;
    }
}
