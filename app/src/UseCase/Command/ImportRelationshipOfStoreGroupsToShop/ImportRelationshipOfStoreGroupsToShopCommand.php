<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportRelationshipOfStoreGroupsToShop;

use Test\PhpServicesBundle\Bus\CommandBase;

final readonly class ImportRelationshipOfStoreGroupsToShopCommand implements CommandBase
{
    /**
     * @param ImportRelationshipOfStoreGroupsToShopItem[] $items
     */
    public function __construct(public array $items)
    {
    }
}
