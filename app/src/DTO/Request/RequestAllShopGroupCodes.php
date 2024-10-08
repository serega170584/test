<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\ValueObject\ConsumerCode;

final readonly class RequestAllShopGroupCodes
{
    public function __construct(
        public ConsumerCode $consumerCode,
        public bool $onlyActive
    ) {
    }
}
