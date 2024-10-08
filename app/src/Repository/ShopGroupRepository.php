<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Filter\ShopGroupFilter;
use App\DTO\Query\AllShopGroups\ShopGroup;
use App\DTO\Request\RequestAllRelatedShopsGroups;
use App\DTO\Request\RequestAllShopGroupCodes;
use App\DTO\Response\ExportShopGroup\ShopGroupDto;
use App\Entity\ShopGroupEntity;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<ShopGroupEntity>
 */
class ShopGroupRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopGroupEntity::class);
    }

    public function save(ShopGroupEntity $entity, bool $flush = false): void
    {
        $this->trySave($entity, $flush);
    }

    public function remove(ShopGroupEntity $entity, bool $flush = false): void
    {
        $entity->getParent()?->removeChildren($entity);

        foreach ($entity->getChildren() as $child) {
            $child->setParent(null);
        }

        foreach ($entity->getConsumers() as $consumer) {
            $consumer->removeShopGroup($entity);
        }

        $this->tryRemove($entity, $flush);
    }

    public function findByShopIdAndConsumerId(string $ufXmlId, int $consumerId): ?ShopGroupEntity
    {
        $query = $this->_em->createQuery(
            'SELECT sg
                FROM App\Entity\ShopGroupEntity sg
                INNER JOIN sg.shops s 
                INNER JOIN sg.consumers c 
                WHERE s.ufXmlId = :uf_xml_id
                 AND c.id = :consumer_entity_id'
        )
            ->setParameters([
                'uf_xml_id' => $ufXmlId,
                'consumer_entity_id' => $consumerId,
            ]);

        $result = $query->execute();

        return count($result) ? array_pop($result) : null;
    }

    public function findAllShopGroupCodes(RequestAllShopGroupCodes $request): array
    {
        if ($request->consumerCode->value()) {
            $qb = $this->createQueryBuilder('sg')
                       ->select('sg.code')
                       ->leftJoin('sg.consumers', 'consumer')
                       ->where('consumer.code = :consumerCode')
                       ->setParameter('consumerCode', $request->consumerCode);

            if ($request->onlyActive) {
                $qb->andWhere('sg.active = true');
            }

            $result = $qb->getQuery()->getScalarResult();

            return array_column($result, 'code');
        }

        return [];
    }

    public function findAllRelatedShopsGroups(RequestAllRelatedShopsGroups $request): array
    {
        if ($request->shopGroupCode) {
            $qb = $this->createQueryBuilder('sg')
                       ->leftJoin('sg.consumers', 'consumers')
                       ->where('consumers.code = :consumerCode')
                       ->andWhere('sg.code = :shopGroupCode')
                       ->setParameter('consumerCode', $request->consumerCode)
                       ->setParameter('shopGroupCode', $request->shopGroupCode);

            if ($request->onlyActive) {
                $qb->andWhere('sg.active = true');
            }

            if ($request->excludeShopGroupCodes) {
                $qb->andWhere('not relatedShopGroup.code in (:excludeShopGroupCodes)')
                   ->setParameter('excludeShopGroupCodes', $request->excludeShopGroupCodes);
            }

            $relatedShopGroups = [];
            $codes = $this->findChildShopGroupCodes(clone $qb);

            if ($parentCode = $this->findParentShopGroupCode(clone $qb)) {
                $codes[] = $parentCode;
            }

            $request->excludeShopGroupCodes[] = $request->shopGroupCode;
            $request->excludeShopGroupCodes = array_merge($request->excludeShopGroupCodes, $codes);

            foreach ($codes as $code) {
                $relatedShopGroups[] = $this->findAllRelatedShopsGroups(
                    new RequestAllRelatedShopsGroups(
                        $request->consumerCode,
                        $code,
                        $request->onlyActive,
                        $request->excludeShopGroupCodes
                    )
                );
            }

            return array_merge($codes, ...$relatedShopGroups);
        }

        return [];
    }

    private function findChildShopGroupCodes(QueryBuilder $baseQueryBuilder): array
    {
        $result = $baseQueryBuilder->select('relatedShopGroup.code as code')
                                   ->innerJoin('sg.children', 'relatedShopGroup')
                                   ->getQuery()->getScalarResult();

        return $result ? array_column($result, 'code') : [];
    }

    private function findParentShopGroupCode(QueryBuilder $baseQueryBuilder): ?string
    {
        $parentRow = $baseQueryBuilder->select('relatedShopGroup.code as code')
                                      ->innerJoin('sg.parent', 'relatedShopGroup')
                                      ->getQuery()->getOneOrNullResult();

        if ($parentRow) {
            return $parentRow['code'];
        }

        return null;
    }

    /**
     * @return ShopGroupDto[]
     */
    public function findAllWithConsumers(): array
    {
        $shopGroups = [];

        $query = $this->_em->createQueryBuilder()
                           ->select('sg.code, sg.title, sg.description, parent_sg.code AS parent_code, sg.active, sg.fiasId AS fias_id, c.code AS consumer_code')
                           ->from(ShopGroupEntity::class, 'sg')
                           ->leftJoin('sg.parent', 'parent_sg')
                           ->leftJoin('sg.consumers', 'c');

        $result = $query
            ->getQuery()
            ->getArrayResult();

        foreach ($result as $shopGroupArr) {
            $shopGroupCode = $shopGroupArr['code'];
            $shopGroupArr['consumer_code'] = (string) $shopGroupArr['consumer_code'];
            if (array_key_exists($shopGroupCode, $shopGroups)) {
                /** @var ShopGroupDto $shopGroupDto */
                $shopGroupDto = $shopGroups[$shopGroupCode];
                $shopGroupDto->setConsumers($shopGroupArr['consumer_code']);
            } else {
                $shopGroups[$shopGroupCode] = new ShopGroupDto(
                    $shopGroupCode,
                    $shopGroupArr['title'],
                    $shopGroupArr['description'],
                    $shopGroupArr['parent_code'],
                    $shopGroupArr['active'],
                    $shopGroupArr['consumer_code'],
                    $shopGroupArr['fias_id'],
                );
            }
        }

        return $shopGroups;
    }

    public function findOneByCode(string $code): ?ShopGroupEntity
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * @return iterable<int, ShopGroupEntity>
     */
    public function findByFilter(ShopGroupFilter $filter): iterable
    {
        $qb = $this->createQueryBuilder('sg');

        if ($filter->consumerId) {
            $qb->distinct() // Без distinct падает с ошибкой при join
               ->innerJoin('sg.consumers', 'consumer')
               ->andWhere('consumer.id = :consumerId')
               ->setParameter('consumerId', $filter->consumerId);
        }
        if ($filter->codes) {
            $qb->andWhere('sg.code in (:shopGroupsCodes)')
               ->setParameter('shopGroupsCodes', $filter->codes);
        }
        if ($filter->fiasIds) {
            $qb->andWhere('sg.fiasId in (:fiasIds)')
               ->setParameter('fiasIds', $filter->fiasIds);
        }
        if ($filter->onlyActive) {
            $qb->andWhere('sg.active = true');
        }

        return $qb->getQuery()->toIterable();
    }

    /**
     * @return array<ShopGroupEntity>
     */
    public function findAllActiveByFiasIdAndConsumerId(string $fiasId, int $consumerId): array
    {
        return $this->createQueryBuilder('sg')
                    ->where('sg.active = true')
                    ->innerJoin('sg.consumers', 'c')
                    ->andWhere('sg.fiasId = :fiasId')
                    ->setParameter('fiasId', $fiasId)
                    ->andWhere('c.id = :consumerId')
                    ->setParameter('consumerId', $consumerId)
                    ->getQuery()
                    ->getResult();
    }

    /**
     * @return iterable<ShopGroup>
     *
     * @throws Exception
     * @throws \JsonException
     */
    public function getWithShops(bool $isShopGroupActive, $lastShopGroupCode = null, $limit = 0): iterable
    {
        $query = $this->createQueryBuilder('sg')
            ->select('sg.code, sg.active, sg.isDistr, JSON_AGG_CUSTOM(shops.ufXmlId) as shop_codes')
            ->join('sg.shops', 'shops');

        $query->groupBy('sg.code, sg.active, sg.isDistr');
        $query->having('sg.active = :isShopGroupActive');
        $query->setParameter('isShopGroupActive', $isShopGroupActive);

        if ($lastShopGroupCode) {
            $query->andHaving('sg.code > :lastShopGroupCode');
            $query->setParameter('lastShopGroupCode', $lastShopGroupCode);
        }

        if ($limit) {
            $query->setMaxResults($limit);
        }

        $query->orderBy('sg.code');

        $res = $query->getQuery()->getResult(Query::HYDRATE_ARRAY);

        foreach ($res as $row) {
            yield new ShopGroup(
                $row['code'],
                (bool) $row['active'],
                (bool) $row['isDistr'],
                json_decode($row['shop_codes'], true, 512, JSON_THROW_ON_ERROR)
            );
        }
    }
}
