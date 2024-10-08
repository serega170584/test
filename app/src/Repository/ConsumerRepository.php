<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Response\ExportShopGroup\ConsumerDto;
use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\ValueObject\ConsumerCode;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<ConsumerEntity>
 */
class ConsumerRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumerEntity::class);
    }

    public function save(ConsumerEntity $entity, bool $flush = false): void
    {
        $this->trySave($entity, $flush);
    }

    public function remove(ConsumerEntity $entity, bool $flush = false): void
    {
        foreach ($entity->getShopGroups() as $shopGroup) {
            $entity->removeShopGroup($shopGroup);
        }
        $this->tryRemove($entity, $flush);
    }

    public function findByCode(ConsumerCode $code): ?ConsumerEntity
    {
        return $this->findOneBy(['code' => $code->value()]);
    }

    /**
     * @return ConsumerDto[]
     */
    public function findAllForExport(): array
    {
        $query = $this->_em->createQueryBuilder()
            ->select(
                sprintf(
                    'new %s(c.code, c.name, c.description)',
                    ConsumerDto::class
                )
            )
            ->from(ConsumerEntity::class, 'c');

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * @param ShopGroupEntity[] $shopGroups
     *
     * @throws Exception
     */
    public function insertConsumerShopGroupRelations(ConsumerEntity $consumer, array $shopGroups): void
    {
        $newConsumerShopGroupRows = [];

        foreach ($shopGroups as $shopGroup) {
            $newConsumerShopGroupRows[] = sprintf('(%s, %s)', $consumer->getId(), $shopGroup->getId());
        }

        if ($newConsumerShopGroupRows) {
            $this->_em->getConnection()->executeQuery(
                sprintf('INSERT INTO 
    consumer_entity_shop_group_entity(consumer_entity_id, shop_group_entity_id) 
    VALUES %s
    ON CONFLICT DO NOTHING;', implode(', ', $newConsumerShopGroupRows))
            );
        }
    }

    /**
     * @param string[] $consumerCodes
     *
     * @return ConsumerEntity[]
     */
    public function findByCodesNotIn(array $consumerCodes): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.code NOT IN (:codes)')
            ->setParameter('codes', $consumerCodes)
            ->getQuery()
            ->execute();
    }
}
