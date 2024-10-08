<?php

declare(strict_types=1);

namespace App\UseCase\Command\ImportShopGroups;

use Test\PhpServicesBundle\Bus\CommandBase;

class ImportShopGroupsCommand implements CommandBase
{
    /**
     * @param ImportShopGroupItem[] $items
     */
    public function __construct(public array $items)
    {
    }
}
