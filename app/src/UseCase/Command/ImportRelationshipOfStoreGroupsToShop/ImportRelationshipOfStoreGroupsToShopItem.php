<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportRelationshipOfStoreGroupsToShop;

final readonly class ImportRelationshipOfStoreGroupsToShopItem
{
    public function __construct(public string $shopGroupCode, public string $ufXmlId)
    {
    }
}
