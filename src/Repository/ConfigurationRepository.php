<?php

namespace App\Repository;

use App\Entity\Configuration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Configuration>
 */
class ConfigurationRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Configuration::class);
    }

    public function create(array $data): Configuration
    {
        $entity = new Configuration();

        /** @var Configuration $entity */
        $entity = $this->update($entity, $data);

        return $entity;
    }
}
