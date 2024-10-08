<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetAllShopGroups;

use App\Repository\ShopGroupRepository;
use Doctrine\DBAL\Exception;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;

final readonly class GetAllShopGroupsHandler implements QueryHandlerBase
{
    public function __construct(
        private ShopGroupRepository $shopGroupRepository,
    ) {
    }

    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function __invoke(GetAllShopGroupsQuery $query): iterable
    {
        return $this->shopGroupRepository->getWithShops(
            $query->isShopGroupActive,
            $query->lastSgCode,
            $query->limit
        );
    }
}
