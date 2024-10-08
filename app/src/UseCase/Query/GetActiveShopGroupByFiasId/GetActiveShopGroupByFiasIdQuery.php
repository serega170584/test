<?php

declare(strict_types=1);

namespace App\UseCase\Query\GetActiveShopGroupByFiasId;

use App\ValueObject\ConsumerCode;
use Test\PhpServicesBundle\Bus\QueryBase;

class GetActiveShopGroupByFiasIdQuery implements QueryBase
{
    public function __construct(
        public string $fiasId,
        public ConsumerCode $consumerCode,
        public ?string $consumerVersion = null
    ) {
    }
}
