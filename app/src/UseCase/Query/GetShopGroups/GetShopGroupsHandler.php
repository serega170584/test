<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShopGroups;

use App\DTO\Filter\ShopGroupFilter;
use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\Exception\NotFoundException;
use App\Repository\ConsumerRepository;
use App\Repository\ShopGroupRepository;
use App\Service\ConsumerResolver;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;

readonly class GetShopGroupsHandler implements QueryHandlerBase
{
    public function __construct(
        private ShopGroupRepository $shopGroupRepository,
        private ConsumerRepository $consumerRepository,
        private ConsumerResolver $consumerResolver
    ) {
    }

    /**
     * @return ShopGroupEntity[]
     */
    public function __invoke(GetShopGroupsQuery $query): iterable
    {
        $consumerCode = $this->consumerResolver->resolveConsumerCodeByConsumerVersion($query->consumerCode, $query->consumerVersion);
        $consumer = $this->consumerRepository->findByCode($consumerCode);
        if (!$consumer) {
            throw new NotFoundException("Not found consumer with code [{$consumerCode}]");
        }

        return $this->shopGroupRepository->findByFilter($this->createFilter($consumer, $query));
    }

    private function createFilter(ConsumerEntity $consumerEntity, GetShopGroupsQuery $query): ShopGroupFilter
    {
        $filter = new ShopGroupFilter();
        $filter->consumerId = $consumerEntity->getId();
        $filter->codes = $query->shopGroupCodes;
        $filter->fiasIds = $query->fiasIds;
        $filter->onlyActive = true;

        return $filter;
    }
}
