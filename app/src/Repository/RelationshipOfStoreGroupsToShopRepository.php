<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Query\RelationsShopGroupToShopQueryParams;
use App\DTO\Response\ExportShopGroup\ShopGroupToShopDto;
use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\ValueObject\ConsumerCode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<RelationshipOfStoreGroupsToShopEntity>
 */
class RelationshipOfStoreGroupsToShopRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RelationshipOfStoreGroupsToShopEntity::class);
    }

    public function save(RelationshipOfStoreGroupsToShopEntity $entity, bool $flush = false): void
    {
        $this->trySave($entity, $flush);
    }

    public function remove(RelationshipOfStoreGroupsToShopEntity $entity, bool $flush = false): void
    {
        $entity->getShopGroup()->removeShop($entity->getUfXmlId());
        $this->tryRemove($entity, $flush);
    }

    public function removeByShopXmlId(string $shopXmlId): void
    {
        $this->createQueryBuilder('s')
             ->delete()
             ->where('s.ufXmlId = :shopXmlId')
             ->setParameter('shopXmlId', $shopXmlId)
             ->getQuery()
             ->execute();
    }

    public function findShopsCodesByConsumerIdAndShopGroupId(ConsumerCode $consumerCode, string $shopGroupCode): array
    {
        $result = $this->createQueryBuilder('s')
                       ->select('s.ufXmlId')
                       ->leftJoin('s.shopGroup', 'sg')
                       ->leftJoin('sg.consumers', 'c')
                       ->where('c.code = :consumerCode')
                       ->andWhere('sg.code = :shopGroupCode')
                       ->setParameters([
                           'consumerCode' => $consumerCode,
                           'shopGroupCode' => $shopGroupCode,
                       ])
                       ->addOrderBy('s.ufXmlId', 'ASC')
                       ->getQuery()->getScalarResult();

        return array_column($result, 'ufXmlId');
    }

    /**
     * @return ShopGroupToShopDto[]
     */
    public function getAllWithCategoryCode(): array
    {
        $query = $this->_em->createQueryBuilder()
            ->select(sprintf(
                'new %s(r.ufXmlId, s.code)',
                ShopGroupToShopDto::class
            )
            )
            ->from(RelationshipOfStoreGroupsToShopEntity::class, 'r')
            ->innerJoin('r.shopGroup', 's');

        return $query
            ->getQuery()
            ->getResult();
    }

    public function deleteNotIn(array $validShopGroupIDs): void
    {
        $IDstring = implode(',', $validShopGroupIDs);

        $this->_em->getConnection()->executeQuery(
            sprintf('DELETE FROM relationship_of_store_groups_to_shop WHERE shop_group_id NOT IN (%s)', $IDstring)
        );
    }

    /**
     * @return iterable<RelationshipOfStoreGroupsToShopEntity>
     */
    public function findByParams(RelationsShopGroupToShopQueryParams $params): iterable
    {
        $query = $this->createQueryBuilder('rsgs');

        if ($params->shopGroupCode) {
            $query
                ->leftJoin('rsgs.shopGroup', 'sg')
                ->where('sg.code = :shopGroupCode')
                ->setParameters([
                    'shopGroupCode' => $params->shopGroupCode,
                ]);
            if ($params->onlyActiveShopGroup) {
                $query->andWhere('sg.active = true');
            }
        }

        return $query->getQuery()->toIterable();
    }
}
