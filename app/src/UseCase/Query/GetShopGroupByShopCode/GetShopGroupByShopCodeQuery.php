<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShopGroupByShopCode;

use App\ValueObject\ConsumerCode;
use Test\PhpServicesBundle\Bus\QueryBase;

class GetShopGroupByShopCodeQuery implements QueryBase
{
    public function __construct(
        public string $ufXmlId,
        public ConsumerCode $consumerCode,
        public ?string $consumerVersion = null
    ) {
    }
}
