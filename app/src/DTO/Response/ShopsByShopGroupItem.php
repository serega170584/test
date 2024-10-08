<?php

declare(strict_types=1);

namespace App\DTO\Response;

final readonly class ShopsByShopGroupItem
{
    public function __construct(
        public string $shopGroupCode,
        /**
         * @var string[]
         */
        public array $shopsCodes,
    ) {
    }
}
