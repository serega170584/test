<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShopGroupsByShopGroupCode;

use App\DTO\Filter\ShopGroupFilter;
use App\Entity\ShopGroupEntity;
use App\Repository\ShopGroupRepository;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;

readonly class GetShopGroupByShopGroupCodeHandler implements QueryHandlerBase
{
    public function __construct(
        private ShopGroupRepository $shopGroupRepository,
    ) {
    }

    /**
     * @return ShopGroupEntity[]
     */
    public function __invoke(GetShopGroupByShopGroupCodeQuery $query): iterable
    {
        return $this->shopGroupRepository->findByFilter($this->createFilter($query));
    }

    private function createFilter(GetShopGroupByShopGroupCodeQuery $query): ShopGroupFilter
    {
        $filter = new ShopGroupFilter();
        $filter->codes = [$query->shopGroupCode];
        $filter->onlyActive = true;

        return $filter;
    }
}
