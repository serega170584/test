<?php

declare(strict_types=1);

namespace App\DTO\Response;

final readonly class ShopsByShopGroupResponse
{
    public function __construct(
        /**
         * @var ShopsByShopGroupItem[]
         */
        public array $items,
    ) {
    }
}
