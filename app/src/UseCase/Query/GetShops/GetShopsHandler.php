<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShops;

use App\DTO\Query\RelationsShopGroupToShopQueryParams;
use App\Entity\RelationshipOfStoreGroupsToShopEntity;
use App\Enum\CacheKeyTemplateEnum;
use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;
use Test\PhpServicesBundle\Cache\Cache;

readonly class GetShopsHandler implements QueryHandlerBase
{
    public function __construct(
        private RelationshipOfStoreGroupsToShopRepository $relationshipOfStoreGroupsToShopRepository,
        private Cache $cache,
    ) {
    }

    /**
     * @return iterable<RelationshipOfStoreGroupsToShopEntity>
     */
    public function __invoke(GetShopsQuery $query): iterable
    {
        $params = new RelationsShopGroupToShopQueryParams(shopGroupCode: $query->shopGroupCode);

        return $this->cache->call(
            CacheKeyTemplateEnum::GET_SHOPS->createKey($params->shopGroupCode),
            fn () => iterator_to_array($this->relationshipOfStoreGroupsToShopRepository->findByParams($params))
        );
    }
}
