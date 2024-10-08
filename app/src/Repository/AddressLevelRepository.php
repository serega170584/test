<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AddressLevelEntity;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<AddressLevelEntity>
 *
 * @method AddressLevelEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method AddressLevelEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method AddressLevelEntity[]    findAll()
 * @method AddressLevelEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressLevelRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AddressLevelEntity::class);
    }

    public function save(AddressLevelEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AddressLevelEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return AddressLevelEntity[]
     */
    public function findSecondLevels(): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.level = :level')
            ->setParameter('level', 2)
            ->getQuery()
            ->getResult();
    }
}
