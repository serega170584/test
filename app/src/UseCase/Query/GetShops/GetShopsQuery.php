<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetShops;

use Test\PhpServicesBundle\Bus\QueryBase;

class GetShopsQuery implements QueryBase
{
    public function __construct(
        public string $shopGroupCode
    ) {
    }
}
