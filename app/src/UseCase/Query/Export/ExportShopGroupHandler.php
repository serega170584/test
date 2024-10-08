<?php

declare(strict_types=1);

namespace App\UseCase\Query\Export;

use App\DTO\Response\ExportShopGroup\ExportShopGroupDto;
use App\Repository\ConsumerRepository;
use App\Repository\RelationshipOfStoreGroupsToShopRepository;
use App\Repository\ShopGroupRepository;
use Test\PhpServicesBundle\Bus\QueryHandlerBase;

class ExportShopGroupHandler implements QueryHandlerBase
{
    public function __construct(
        private readonly ShopGroupRepository $shopGroupRepository,
        private readonly ConsumerRepository $consumerRepository,
        private readonly RelationshipOfStoreGroupsToShopRepository $relationshipOfStoreGroupsToShopRepository
    ) {
    }

    public function __invoke(ExportShopGroupQuery $query): ExportShopGroupDto
    {
        $shopGroupDtos = $this->shopGroupRepository->findAllWithConsumers();
        $consumerDtos = $this->consumerRepository->findAllForExport();
        $shopGroupToShopRelationsDtos = $this->relationshipOfStoreGroupsToShopRepository->getAllWithCategoryCode();

        $downloadFileDto = new ExportShopGroupDto();
        $downloadFileDto->setShopGroups($shopGroupDtos);
        $downloadFileDto->setConsumers($consumerDtos);
        $downloadFileDto->setShopGroupToShop($shopGroupToShopRelationsDtos);

        return $downloadFileDto;
    }
}
