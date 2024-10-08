<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShopGroupsByShopGroupCode;

use Test\PhpServicesBundle\Bus\QueryBase;

class GetShopGroupByShopGroupCodeQuery implements QueryBase
{
    public function __construct(
        public string $shopGroupCode,
    ) {
    }
}
