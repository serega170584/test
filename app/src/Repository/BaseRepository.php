<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of object
 *
 * @template-extends ServiceEntityRepository<T>
 */
class BaseRepository extends ServiceEntityRepository
{
    /**
     * @param string|class-string<object> $entityClass
     */
    public function __construct(ManagerRegistry $registry, string $entityClass = '')
    {
        parent::__construct($registry, $entityClass);
    }

    protected function trySave(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    protected function tryRemove(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function truncate(): void
    {
        $connection = $this->getEntityManager()->getConnection();

        $connection->executeStatement(
            'truncate table '.$this->getClassMetadata()->getTableName().' restart identity cascade'
        );
    }
}
