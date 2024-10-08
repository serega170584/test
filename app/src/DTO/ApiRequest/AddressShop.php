<?php

declare(strict_types=1);

namespace App\DTO\ApiRequest;

final readonly class AddressShop
{
    public function __construct(
        public int $addressId,
        public int $shopId,
    ) {
    }
}
