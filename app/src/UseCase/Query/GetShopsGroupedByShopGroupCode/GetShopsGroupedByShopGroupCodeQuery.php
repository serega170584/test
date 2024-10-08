<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShopsGroupedByShopGroupCode;

use App\ValueObject\ConsumerCode;
use Test\PhpServicesBundle\Bus\QueryBase;

final readonly class GetShopsGroupedByShopGroupCodeQuery implements QueryBase
{
    public function __construct(
        public ConsumerCode $consumerCode,
        public string $shopGroupCode = '',
        public bool $recursive = false
    ) {
    }
}
