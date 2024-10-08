<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ShopEntity;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<ShopEntity>
 *
 * @method ShopEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopEntity[]    findAll()
 * @method ShopEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopEntity::class);
    }

    public function save(ShopEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ShopEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return iterable<ShopEntity>
     */
    public function getAllShops(): iterable
    {
        return $this->createQueryBuilder('s')
            ->getQuery()
            ->toIterable();
    }

    /**
     * @return iterable<ShopEntity>
     */
    public function getNonDistributorShops(): iterable
    {
        return $this->createQueryBuilder('s')
            ->where('s.isDistr = :isDistr')
            ->setParameter('isDistr', false)
            ->getQuery()
            ->toIterable();
    }
}
