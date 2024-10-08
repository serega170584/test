<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AddressEntity;
use App\Entity\AddressLevelEntity;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<AddressEntity>
 *
 * @method AddressEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method AddressEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method AddressEntity[]    findAll()
 * @method AddressEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AddressEntity::class);
    }

    public function save(AddressEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AddressEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param AddressLevelEntity[] $levels
     */
    public function findLevelRows(array $levels): iterable
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.level IN (:levels)')
            ->setParameter('levels', $levels)
            ->getQuery()
            ->toIterable();
    }
}
