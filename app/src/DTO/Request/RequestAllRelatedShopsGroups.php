<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\ValueObject\ConsumerCode;

final class RequestAllRelatedShopsGroups
{
    public function __construct(
        public readonly ConsumerCode $consumerCode,
        public readonly string $shopGroupCode,
        public readonly bool $onlyActive,
        public array $excludeShopGroupCodes = []
    ) {
    }
}
