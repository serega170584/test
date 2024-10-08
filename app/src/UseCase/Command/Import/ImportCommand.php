<?php

declare(strict_types=1);

namespace App\UseCase\Command\Import;

use Test\PhpServicesBundle\Bus\CommandBase;

final readonly class ImportCommand implements CommandBase
{
    public function __construct(
        /**
         * @var ImportConsumerItem[]
         */
        public array $consumers,
        /**
         * @var ImportShopGroupItem[]
         */
        public array $shopGroups,
        /**
         * @var ImportRelationshipOfStoreGroupsToShopItem[]
         */
        public array $relationshipOfStoreGroupsToShopItems,
    ) {
    }
}
