<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AddressEntity;
use App\Entity\AddressToAddressEntity;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<AddressToAddressEntity>
 *
 * @method AddressToAddressEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method AddressToAddressEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method AddressToAddressEntity[]    findAll()
 * @method AddressToAddressEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressToAddressEntityRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AddressToAddressEntity::class);
    }

    public function save(AddressToAddressEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AddressToAddressEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param AddressEntity[] $addresses
     *
     * @return AddressToAddressEntity[]
     */
    public function getAddressRelationships(array $addresses): array
    {
        return $this->createQueryBuilder('a2a')
            ->andWhere('a2a.address IN (:addresses)')
            ->setParameter('addresses', $addresses)
            ->andWhere('a2a.parent IN (:parents)')
            ->setParameter('parents', $addresses)
            ->getQuery()
            ->getResult();
    }
}
