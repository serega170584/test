<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShopGroups;

use App\ValueObject\ConsumerCode;
use Test\PhpServicesBundle\Bus\QueryBase;

class GetShopGroupsQuery implements QueryBase
{
    public function __construct(
        public ConsumerCode $consumerCode,
        public array $shopGroupCodes,
        public array $fiasIds,
        public ?string $consumerVersion = null
    ) {
    }
}
