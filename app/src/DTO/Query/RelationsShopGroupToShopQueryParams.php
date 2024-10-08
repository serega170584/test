<?php

declare(strict_types=1);

namespace App\DTO\Query;

final readonly class RelationsShopGroupToShopQueryParams
{
    public function __construct(
        public ?string $shopGroupCode = null,
        public bool $onlyActiveShopGroup = true,
    ) {
    }
}
