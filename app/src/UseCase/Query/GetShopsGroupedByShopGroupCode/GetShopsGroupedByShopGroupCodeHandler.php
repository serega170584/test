<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShopsGroupedByShopGroupCode;

use App\DTO\Request\RequestAllRelatedShopsGroups;
use App\DTO\Request\RequestAllShopGroupCodes;
use App\DTO\Response\ShopsByShopGroupItem;
use App\DTO\Response\ShopsByShopGroupResponse;
use App\Enum\CacheKeyTemplateEnum;
use App\Repository\ConsumerRepository;
use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use App\Repository\ShopGroupRepository;
use App\ValueObject\ConsumerCode;
use Doctrine\ORM\EntityNotFoundException;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;
use Test\PhpServicesBundle\Cache\Cache;

final readonly class GetShopsGroupedByShopGroupCodeHandler implements QueryHandlerBase
{
    public function __construct(
        private RelationshipOfStoreGroupsToShopRepository $shopsRepository,
        private ShopGroupRepository $shopGroupRepository,
        private ConsumerRepository $consumerRepository,
        private Cache $cache,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     * @throws \JsonException
     */
    public function __invoke(GetShopsGroupedByShopGroupCodeQuery $query): ShopsByShopGroupResponse
    {
        $this->checkConsumer($query->consumerCode);

        $result = [];
        $shopGroupsCodes = $this->findShopGroupsCodes($query);

        foreach ($shopGroupsCodes as $shopGroupCode) {
            if ($response = $this->createShopsByShopGroupResponse($query->consumerCode, $shopGroupCode)) {
                $result[] = $response;
            }
        }

        return new ShopsByShopGroupResponse($result);
    }

    /**
     * @throws EntityNotFoundException
     */
    private function checkConsumer(ConsumerCode $consumerCode): void
    {
        $result = $this->cache->call(
            CacheKeyTemplateEnum::CONSUMER_FOUND->createKey($consumerCode),
            function () use ($consumerCode) {
                return null !== $this->consumerRepository->findByCode($consumerCode);
            }
        );

        if (!$result) {
            throw new EntityNotFoundException('Consumer ['.$consumerCode.'] not found');
        }
    }

    private function findShopGroupsCodes(GetShopsGroupedByShopGroupCodeQuery $query): array
    {
        if ($query->shopGroupCode) {
            if ($query->recursive) {
                return $this->findRecursiveShopGroupsCodes($query);
            }

            return [$query->shopGroupCode];
        }

        return $this->cache->call(
            CacheKeyTemplateEnum::CONSUMER_ALL_SHOPS_GROUPS->createKey($query->consumerCode),
            function () use ($query) {
                return $this->shopGroupRepository->findAllShopGroupCodes(
                    new RequestAllShopGroupCodes($query->consumerCode, true)
                );
            }
        );
    }

    private function findRecursiveShopGroupsCodes(GetShopsGroupedByShopGroupCodeQuery $query): array
    {
        $linkToCacheId = CacheKeyTemplateEnum::CONSUMER_LINK_TO_RELATED_SHOPS_GROUPS->createKey(
            $query->consumerCode,
            $query->shopGroupCode
        );

        if ($this->cache->has($linkToCacheId)) {
            $recursiveCacheId = $this->cache->get($linkToCacheId);
        } else {
            $recursiveCacheId = CacheKeyTemplateEnum::CONSUMER_RELATED_SHOPS_GROUPS->createKey(
                $query->consumerCode,
                $query->shopGroupCode
            );
        }

        return $this->cache->call(
            $recursiveCacheId,
            function () use ($query, $recursiveCacheId) {
                $allRelatedShopsGroups = $this->shopGroupRepository->findAllRelatedShopsGroups(
                    new RequestAllRelatedShopsGroups($query->consumerCode, $query->shopGroupCode, true)
                );

                foreach ($allRelatedShopsGroups as $code) {
                    $this->cache->set(
                        CacheKeyTemplateEnum::CONSUMER_LINK_TO_RELATED_SHOPS_GROUPS->createKey(
                            $query->consumerCode,
                            $code
                        ),
                        $recursiveCacheId
                    );
                }

                $allRelatedShopsGroups[] = $query->shopGroupCode;

                return $allRelatedShopsGroups;
            }
        );
    }

    private function createShopsByShopGroupResponse(
        ConsumerCode $consumerCode,
        string $shopGroupCode
    ): ?ShopsByShopGroupItem {
        return $this->cache->call(
            CacheKeyTemplateEnum::CONSUMER_SHOPS_GROUPS->createKey($consumerCode, $shopGroupCode),
            function () use ($consumerCode, $shopGroupCode) {
                $shops = $this->shopsRepository->findShopsCodesByConsumerIdAndShopGroupId(
                    $consumerCode,
                    $shopGroupCode
                );

                if ($shops) {
                    return new ShopsByShopGroupItem($shopGroupCode, $shops);
                }

                return null;
            }
        );
    }
}
